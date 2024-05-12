<?php

use Borsch\Cache\Cache;
use Borsch\Cache\Exception\InvalidKeyException;
use Borsch\Cache\Logger\VoidLogger;
use Borsch\Cache\Pool\InMemoryCacheItemPool;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;

test('get() returns the value', function() {
    expect($this->cache->get('foo'))->toBe('bar')
        ->and($this->cache->get('bar'))->toBe('baz');
});

test('get() throws exception with invalid character key', function () {
    $this->cache->get('f?!@#o');
})->throws(InvalidKeyException::class);

test('get() throws exception with invalid too long key', function () {
    $this->cache->get('fooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo');
})->throws(InvalidKeyException::class);

test('get() throws exception with invalid empty key', function () {
    $this->cache->get('');
})->throws(InvalidKeyException::class);

test('set() returns the value', function() {
    expect($this->cache->has('demo'))->toBeFalse();
    $this->cache->set('demo', 'val');
    expect($this->cache->has('demo'))->toBeTrue()
        ->and($this->cache->get('demo'))->toBe('val');
});

test('set() throws exception with invalid character key', function () {
    $this->cache->set('f?!@#o', '');
})->throws(InvalidKeyException::class);

test('delete() returns boolean', function() {
    expect($this->cache->has('foo'))->toBeTrue()
        ->and($this->cache->delete('foo'))->toBeTrue()
        ->and($this->cache->has('foo'))->toBeFalse();
});

test('delete() throws exception with invalid character key', function () {
    $this->cache->delete('f?!@#o', '');
})->throws(InvalidKeyException::class);

test('clear() removes all entries', function () {
    expect($this->cache->clear())->toBeTrue()
        ->and($this->cache->has('foo'))->toBeFalse()
        ->and($this->cache->has('bar'))->toBeFalse();
});

test('getMultiple() to return multiple entries', function() {
    expect($this->cache->getMultiple(['foo', 'bar']))
        ->toBeArray()
        ->toHaveCount(2)
        ->toBe(['foo' => 'bar', 'bar' => 'baz']);
});

test('setMultiple() sets multiple entries', function() {
    expect($this->cache->setMultiple(['demo' => 'val', 'test' => 42]))->toBeTrue()
        ->and($this->cache->has('demo'))->toBeTrue()
        ->and($this->cache->has('test'))->toBeTrue()
        ->and($this->cache->getMultiple(['demo', 'test']))
            ->toBeArray()
            ->toHaveCount(2)
            ->toBe(['demo' => 'val', 'test' => 42]);
});


test('setMultiple() sets multiple entries with TTL', function() {
    $interval = new DateInterval('PT5M');

    expect($this->cache->setMultiple(['demo' => 'val'], $interval))->toBeTrue()
        ->and($this->cache->has('demo'))->toBeTrue()
        ->and($this->cache->get('demo'))->toBe('val');

    $interval->invert = true;

    expect($this->cache->setMultiple(['test' => 42], $interval))->toBeTrue()
        ->and($this->cache->has('test'))->toBeTrue()
        ->and($this->cache->get('test'))->toBeNull();
});

test('deleteMultiple() removes items', function () {
    expect($this->cache->has('foo'))->toBeTrue()
        ->and($this->cache->has('bar'))->toBeTrue()
        ->and($this->cache->deleteMultiple(['foo', 'bar']))->toBeTrue()
        ->and($this->cache->has('foo'))->toBeFalse()
        ->and($this->cache->has('bar'))->toBeFalse();

});

test('has() returns true on existing entry', function () {
    expect($this->cache->has('foo'))->toBeTrue();
});

test('has() returns false on non-existing entry', function () {
    expect($this->cache->has('demo'))->toBeFalse();
});

test('has() throws exception with invalid character key', function () {
    $this->cache->has('f?!@#o');
})->throws(InvalidKeyException::class);

test('tryGetValue()', function () {
    $falsy = new class extends InMemoryCacheItemPool
    {
        public function save(CacheItemInterface $item): bool { throw new Exception(); }
        public function deleteItem(string $key): bool { throw new Exception(); }
        public function clear(): bool { throw new Exception(); }
    };

    $extended = new class($falsy) extends Cache
    {
        public function testTryGetValue(callable $callback, mixed $default = null): mixed { return $this->tryGetValue($callback, $default); }

        public function cacheException() {
            $this->testTryGetValue(fn() => throw new InvalidKeyException());
        }
    };

    expect($extended->testTryGetValue(fn() => throw new RuntimeException()))->toBeNull();
    expect($extended->set('foo', 'bar'))->toBeFalse();
    expect($extended->delete('foo'))->toBeFalse();
    expect($extended->clear())->toBeFalse();
});

test('tryGetValue() exception', function () {
    $extended = new class(new InMemoryCacheItemPool()) extends Cache
    {
        public function testTryGetValue(callable $callback, mixed $default = null): mixed { return $this->tryGetValue($callback, $default); }

        public function cacheException() {
            $this->testTryGetValue(fn() => throw new InvalidKeyException());
        }
    };

    $extended->cacheException();
})->throws(InvalidKeyException::class);

test('tryGetValue() logger', function () {
    $logger = new class extends VoidLogger
    {

        public bool $has_logged = false;
        public string $logged_message = '';

        public function alert(Stringable|string $message, array $context = []): void {
            $this->has_logged = true;
            $this->logged_message = strtr($message, ['{message}' => $context['message']]);
        }
    };

    $extended = new class(new InMemoryCacheItemPool(), $logger) extends Cache
    {
        public function cacheException() {
            $this->tryGetValue(fn() => throw new Exception('test message'));
        }
        public function getLogger(): LoggerInterface
        {
            return $this->logger;
        }
    };

    $extended->cacheException();

    expect($extended->getLogger()->has_logged)->toBeTrue();
    expect($extended->getLogger()->logged_message)->toBe('An error occurred with cache: test message');
});
