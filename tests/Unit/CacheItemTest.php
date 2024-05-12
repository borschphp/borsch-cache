<?php

use Borsch\Cache\CacheItem;
use Psr\Cache\CacheItemInterface;

test('getKey() returns the key', function () {
    $item = new CacheItem('myKey');
    expect($item->getKey())->toBe('myKey');
});

test('get() returns the value', function () {
    $item = new CacheItem('myKey', 'myValue');
    expect($item->get())->toBe('myValue');
});

test('isHit() validates DateTimeInterface', function () {
    $item = new CacheItem('myKey', 'myValue', new DateTime(date('Y-m-d H:i:s', strtotime('+1 day'))));
    expect($item->isHit())->toBeTrue();

    $item = new CacheItem('myKey', 'myValue', new DateTime(date('Y-m-d H:i:s', strtotime('-1 day'))));
    expect($item->isHit())->toBeFalse();

    $item = new CacheItem('myKey', 'myValue', new DateTime());
    expect($item->isHit())->toBeFalse();
});

test('isHit() validates integer', function () {
    $item = new CacheItem('myKey', 'myValue', strtotime('+1 day'));
    expect($item->isHit())->toBeTrue();

    $item = new CacheItem('myKey', 'myValue', strtotime('-1 day'));
    expect($item->isHit())->toBeFalse();

    $item = new CacheItem('myKey', 'myValue', time());
    expect($item->isHit())->toBeFalse();
});

test('isHit() validates null', function () {
    $item = new CacheItem('myKey', 'myValue', null);
    expect($item->isHit())->toBeTrue();
});

test('set() returns the CacheItem instance', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item_to_test = $item->set('myValue2');

    expect($item_to_test)->toBeInstanceOf(CacheItemInterface::class)->toBe($item)
        ->and($item_to_test->get())->toBe('myValue2');
});

test('expiresAt() to set expiration datetime', function () {
    $item = new CacheItem('myKey', 'myValue');

    $item->expiresAt(new DateTime(date('Y-m-d H:i:s', strtotime('+1 day'))));
    expect($item->isHit())->toBeTrue();

    $item->expiresAt(new DateTime(date('Y-m-d H:i:s', strtotime('-1 day'))));
    expect($item->isHit())->toBeFalse();
});

test('expiresAfter() to set expiration DateInterval', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter(new DateInterval('P1D'));

    expect($item->isHit())->toBeTrue();
});

test('expiresAfter() to set expiration negative DateInterval', function () {
    $interval = new DateInterval('P1D');
    $interval->invert = true;

    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter($interval);

    expect($item->isHit())->toBeFalse();
});

test('expiresAfter() to set expiration integer', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter(3600);

    expect($item->isHit())->toBeTrue();
});

test('expiresAfter() to set expiration negative integer', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter(-3600);

    expect($item->isHit())->toBeFalse();
});

test('expiresAfter() to set expiration null', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter(null);

    expect($item->isHit())->toBeTrue();
});

test('expiresAfter() to set expiration null after a defined expiration', function () {
    $item = new CacheItem('myKey', 'myValue');
    $item->expiresAfter(-3600);
    expect($item->isHit())->toBeFalse();
    $item->expiresAfter(null);
    expect($item->isHit())->toBeTrue();
});
