<?php

use Borsch\Cache\CacheItem;
use Borsch\Cache\Exception\InvalidKeyException;
use Borsch\Cache\Pool\ArrayCacheItemPool;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Cache\CacheItemPoolInterface;

covers(ArrayCacheItemPool::class);

it('can be created', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
        public function getDeferred() { return $this->deferred; }
    };

    expect($pool)->toBeInstanceOf(CacheItemPoolInterface::class)
        ->and($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getDeferred())->toBeInstanceOf(ArrayCollection::class);
});

it('can get item', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key', 'value'));

    expect($pool)->toBeInstanceOf(CacheItemPoolInterface::class)
        ->and($pool->getItem('key'))->toBeInstanceOf(CacheItem::class)
        ->and($pool->getItem('key')->get())->toBe('value');
});

it('can get item with default value', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key', 'value'));

    expect($pool)->toBeInstanceOf(CacheItemPoolInterface::class)
        ->and($pool->getItem('key'))->toBeInstanceOf(CacheItem::class)
        ->and($pool->getItem('key')->get())->toBe('value')
        ->and($pool->getItem('non-existing-key'))->toBeInstanceOf(CacheItem::class)
        ->and($pool->getItem('non-existing-key')->get())->toBeNull();
});

test('getItem() throws exception on empty key', function () {
    $pool = new ArrayCacheItemPool();
    $pool->getItem('');
})->throws(
    InvalidKeyException::class,
    'The key must be a non-empty string.',
    0
);

test('getItem() throws exception on too long key', function () {
    $count = 65;
    $key = str_repeat('k', $count);
    $pool = new ArrayCacheItemPool();
    $pool->getItem($key);
})->throws(
    InvalidKeyException::class,
    sprintf(
        'The key "%s" must have a length of up to 64 characters, %d given.',
        str_repeat('k', 65),
        65
    ),
    0
);

test('getItem() throws exception on non alpha-num key', function () {
    $pool = new ArrayCacheItemPool();
    $pool->getItem('test@key');
})->throws(
    InvalidKeyException::class,
    'The key "test@key" must be a string containing only alphanumeric characters, underscores, and dots.',
    0
);

it('can get multiple items', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->save(new CacheItem('key2', 'value2'));
    $pool->save(new CacheItem('key3', 'value3'));

    expect($pool)->toBeInstanceOf(CacheItemPoolInterface::class)
        ->and($pool->getItems(['key1', 'key2']))->toBeArray()
        ->and($pool->getItems(['key1', 'key2']))->toHaveCount(2)
        ->and($pool->getItems(['key1', 'key2'])['key1']->get())->toBe('value1')
        ->and($pool->getItems(['key1', 'key2'])['key2']->get())->toBe('value2');
});

it('can get multiple items with default value', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key1', 'value1'));

    expect($pool)->toBeInstanceOf(CacheItemPoolInterface::class)
        ->and($pool->getItems(['key1', 'key2']))->toBeArray()
        ->and($pool->getItems(['key1', 'key2']))->toHaveCount(2)
        ->and($pool->getItems(['key1', 'key2'])['key1']->get())->toBe('value1')
        ->and($pool->getItems(['key1', 'key2'])['key2']->get())->toBeNull();
});

it('can check if an item exists', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key1', 'value1'));

    expect($pool->hasItem('key1'))->toBeTrue()
        ->and($pool->hasItem('key2'))->toBeFalse();
});

it('can check if an item exists in deferred', function () {
    $pool = new ArrayCacheItemPool();
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->saveDeferred(new CacheItem('key2', 'value2'));

    expect($pool->hasItem('key1'))->toBeTrue()
        ->and($pool->hasItem('key2'))->toBeTrue()
        ->and($pool->hasItem('key3'))->toBeFalse();
});

it('throws exception on checking an invalid key', function () {
    $pool = new ArrayCacheItemPool();
    $pool->hasItem('');
})->throws(
    InvalidKeyException::class,
    'The key must be a non-empty string.',
    0
);

it('can clear items collection', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
    };
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->save(new CacheItem('key2', 'value2'));
    $pool->save(new CacheItem('key3', 'value3'));

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(3)
        ->and($pool->clear())->toBeTrue()
        ->and($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(0);

});

it('can delete an item', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
    };
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->save(new CacheItem('key2', 'value2'));
    $pool->save(new CacheItem('key3', 'value3'));

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(3)
        ->and($pool->deleteItem('key2'))->toBeTrue()
        ->and($pool->deleteItem('key4'))->toBeFalse()
        ->and($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(2)
        ->and($pool->hasItem('key2'))->toBeFalse()
        ->and($pool->hasItem('key1'))->toBeTrue()
        ->and($pool->hasItem('key3'))->toBeTrue();
});

it('can delete multiple items', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
    };
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->save(new CacheItem('key2', 'value2'));
    $pool->save(new CacheItem('key3', 'value3'));

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(3);

    $pool->deleteItems(['key1', 'key2']);

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(1)
        ->and($pool->hasItem('key1'))->toBeFalse()
        ->and($pool->hasItem('key2'))->toBeFalse()
        ->and($pool->hasItem('key3'))->toBeTrue();
});

it('can save an item', function () {
    $pool = new ArrayCacheItemPool();
    $item = new CacheItem('key1', 'value1');

    expect($pool->save($item))->toBeTrue()
        ->and($pool->getItem('key1'))->toBeInstanceOf(CacheItem::class)
        ->and($pool->getItem('key1')->get())->toBe('value1');
});

it('can save deferred items', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
        public function getDeferred() { return $this->deferred; }
    };
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->saveDeferred(new CacheItem('key2', 'value2'));

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(1)
        ->and($pool->getDeferred())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getDeferred()->count())->toBe(1)
        ->and($pool->getDeferred()->first()->get())->toBe('value2');
});

it('can commit deferred items', function () {
    $pool = new class extends ArrayCacheItemPool {
        public function getCollection() { return $this->items; }
        public function getDeferred() { return $this->deferred; }
    };
    $pool->save(new CacheItem('key1', 'value1'));
    $pool->saveDeferred(new CacheItem('key2', 'value2'));

    expect($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(1)
        ->and($pool->getDeferred())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getDeferred()->count())->toBe(1)
        ->and($pool->getDeferred()->first()->get())->toBe('value2')
        ->and($pool->commit())->toBeTrue()
        ->and($pool->getCollection())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getCollection()->count())->toBe(2)
        ->and($pool->getCollection()->first()->get())->toBe('value1')
        ->and($pool->getCollection()->last()->get())->toBe('value2')
        ->and($pool->getDeferred())->toBeInstanceOf(ArrayCollection::class)
        ->and($pool->getDeferred()->count())->toBe(0);

});
