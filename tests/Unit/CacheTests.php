<?php

use Borsch\Cache\Cache;
use Borsch\Cache\CacheItem;
use Borsch\Cache\Pool\ArrayCacheItemPool;

covers(Cache::class, CacheItem::class, ArrayCacheItemPool::class);

beforeEach(function () {
    $this->cache = new Cache(new ArrayCacheItemPool());
});

it('can be instantiated with a cache item pool', function () {
    expect($this->cache)->toBeInstanceOf(Cache::class);
});

it('can get a value from the cache', function () {
    $this->cache->set('test_key', 'test_value');

    expect($this->cache->get('test_key'))->toBe('test_value');
});

it('returns default value if key does not exist', function () {
    expect($this->cache->get('non_existent_key', 'default_value'))->toBe('default_value');
});

it('can get multiple value from cache', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    $this->cache->set('key3', 'value3');
    expect($this->cache->getMultiple(['key1', 'key2', 'key3']))->toBe([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);
});

it('returns default value for non-existent keys in getMultiple', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    expect($this->cache->getMultiple(['key1', 'key2', 'non_existent_key'], 'default_value'))->toBe([
        'key1' => 'value1',
        'key2' => 'value2',
        'non_existent_key' => 'default_value',
    ]);
});

it('can set a value', function () {
    $this->cache->set('test_key', 'test_value');

    expect($this->cache->get('test_key'))->toBe('test_value');
});

it('can set a value with a DateInterval expiration', function () {
    $this->cache->set('test_key', 'test_value', new DateInterval('PT1H')); // 1 hour

    expect($this->cache->get('test_key'))->toBe('test_value');
});

it('can set a value with an int expiration', function () {
    $this->cache->set('test_key', 'test_value', time() + 3600); // 1 hour

    expect($this->cache->get('test_key'))->toBe('test_value');
});

it('can set multiple values', function () {
    $this->cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);

    expect($this->cache->get('key1'))->toBe('value1')
        ->and($this->cache->get('key2'))->toBe('value2')
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can set multiple values with a DateInterval expiration', function () {
    $this->cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ], new DateInterval('PT1H')); // 1 hour

    expect($this->cache->get('key1'))->toBe('value1')
        ->and($this->cache->get('key2'))->toBe('value2')
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can set multiple values with an int expiration', function () {
    $this->cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ], time() + 3600); // 1 hour

    expect($this->cache->get('key1'))->toBe('value1')
        ->and($this->cache->get('key2'))->toBe('value2')
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can set multiple values with a null expiration', function () {
    $this->cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);

    expect($this->cache->get('key1'))->toBe('value1')
        ->and($this->cache->get('key2'))->toBe('value2')
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can set a value with expired int ttl', function () {
    $this->cache->set('test_key', 'test_value', time() - 3600); // 1 hour ago

    expect($this->cache->get('test_key'))->toBeNull();
});

it('can set a value with expired DateInterval ttl', function () {
    $interval = new DateInterval('PT1H');
    $interval->invert = 1; // Invert the interval to make it negative
    $this->cache->set('test_key', 'test_value', $interval); // 1 hour ago

    expect($this->cache->get('test_key'))->toBeNull();
});

it('can clear cache', function () {
    $this->cache->set('test_key', 'test_value');
    $this->cache->clear();

    expect($this->cache->get('test_key'))->toBeNull();
});

it('can delete a single item', function () {
    $this->cache->set('test_key', 'test_value');

    expect($this->cache->delete('test_key'))->toBeTrue()
        ->and($this->cache->get('test_key'))->toBeNull();
});

it('can delete multiple item', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    $this->cache->set('key3', 'value3');

    expect($this->cache->deleteMultiple(['key1', 'key2']))->toBeTrue()
        ->and($this->cache->get('key1'))->toBeNull()
        ->and($this->cache->get('key2'))->toBeNull()
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can delete multiple item with non-existent keys', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    $this->cache->set('key3', 'value3');

    expect($this->cache->deleteMultiple(['key1', 'non_existent_key']))->toBeFalse()
        ->and($this->cache->get('key1'))->toBeNull()
        ->and($this->cache->get('key2'))->toBe('value2')
        ->and($this->cache->get('key3'))->toBe('value3');
});

it('can check if an item exists', function () {
    $this->cache->set('test_key', 'test_value');

    expect($this->cache->has('test_key'))->toBeTrue()
        ->and($this->cache->has('non_existent_key'))->toBeFalse();
});
