<?php declare(strict_types=1);

namespace Borsch\Tests\Unit;

use Borsch\Cache\CacheItem;
use Borsch\Cache\Pool\ArrayCacheItemPool;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

covers(CacheItem::class, CacheItem::class, ArrayCacheItemPool::class);

it('can be instantiated with key and null value', function () {
    $item = new CacheItem('test_key', null);

    expect($item->getKey())->toBe('test_key')
        ->and($item->get())->toBeNull()
        ->and($item->isHit())->toBeFalse();
});

it('can be instantiated with key, value and no expiration', function () {
    $item = new CacheItem('test_key', 'test_value');

    expect($item->getKey())->toBe('test_key')
        ->and($item->get())->toBe('test_value')
        ->and($item->isHit())->toBeTrue();
});

it('can be instantiated with timestamp expiration', function () {
    $expiration = time() + 3600; // 1 hour from now
    $item = new CacheItem('test_key', 'test_value', $expiration);

    expect($item->isHit())->toBeTrue();
});

it('can be instantiated with DateTime expiration', function () {
    $expiration = (new DateTime())->add(new DateInterval('PT1H')); // 1 hour from now
    $item = new CacheItem('test_key', 'test_value', $expiration);

    expect($item->isHit())->toBeTrue();
});

it('returns false for isHit when expired with timestamp', function () {
    $expiration = time() - 60; // 1 minute ago
    $item = new CacheItem('test_key', 'test_value', $expiration);

    expect($item->isHit())->toBeFalse();
});

it('returns false for isHit when expired with DateTime', function () {
    $expiration = (new DateTime())->sub(new DateInterval('PT1M')); // 1 minute ago
    $item = new CacheItem('test_key', 'test_value', $expiration);

    expect($item->isHit())->toBeFalse();
});

it('returns false for isHit with null value regardless of expiration', function () {
    $expiration = time() + 3600; // 1 hour from now
    $item = new CacheItem('test_key', null, $expiration);

    expect($item->isHit())->toBeFalse();
});

it('sets value and returns self', function () {
    $item = new CacheItem('test_key', null);
    $result = $item->set('new_value');

    expect($result)->toBe($item)
        ->and($item->get())->toBe('new_value');
});

it('expires after DateInterval', function () {
    $item = new class('test_key', 'test_value') extends CacheItem {
        public function getExpiration(): DateTimeInterface { return $this->expiration; }
    };
    $result = $item->expiresAfter(new DateInterval('PT30S')); // 30 seconds

    expect($result)->toBe($item);

    // Should expire in approximately 30 seconds
    $expiration = $item->getExpiration();
    expect($expiration)->toBeInstanceOf(DateTime::class);

    $diff = $expiration->getTimestamp() - time();
    expect($diff)->toBeGreaterThan(29)->toBeLessThan(31);
});

it('expires after seconds', function () {
    $item = new class('test_key', 'test_value') extends CacheItem {
        public function getExpiration(): int { return $this->expiration; }
    };
    $result = $item->expiresAfter(60); // 60 seconds

    expect($result)->toBe($item);

    // Should now be a timestamp roughly 60 seconds in the future
    $expiration = $item->getExpiration();
    expect(is_int($expiration))->toBeTrue();

    $diff = $expiration - time();
    expect($diff)->toBeGreaterThan(59)->toBeLessThan(61);
});

it('expires after null (removes expiration)', function () {
    $item = new class('test_key', 'test_value') extends CacheItem {
        public function getExpiration() { return $this->expiration; }
    };
    $result = $item->expiresAfter(null);

    expect($result)->toBe($item)
        ->and($item->getExpiration())->toBeNull();
});

it('expires at DateTime', function () {
    $date = new DateTime('tomorrow');
    $item = new class('test_key', 'test_value', $date) extends CacheItem {
        public function getExpiration() { return $this->expiration; }
    };
    $result = $item->expiresAt($date);

    expect($result)->toBe($item)
        ->and($item->getExpiration())->toBeInstanceOf(DateTime::class)
        ->and($item->getExpiration()->getTimestamp())->toBe($date->getTimestamp());
});

it('expires at DateTimeImmutable', function () {
    $date = new DateTimeImmutable('tomorrow');
    $item = new class('test_key', 'test_value') extends CacheItem {
        public function getExpiration() { return $this->expiration; }
    };
    $result = $item->expiresAt($date);

    expect($result)->toBe($item)
        ->and($item->getExpiration())->toBeInstanceOf(DateTimeImmutable::class)
        ->and($item->getExpiration()->getTimestamp())->toBe($date->getTimestamp());
});

it('expires at null (removes expiration)', function () {
    $item = new class('test_key', 'test_value', new DateTime('tomorrow')) extends CacheItem {
        public function getExpiration() { return $this->expiration; }
    };
    $result = $item->expiresAt(null);

    expect($result)->toBe($item)
        ->and($item->getExpiration())->toBeNull();
});
