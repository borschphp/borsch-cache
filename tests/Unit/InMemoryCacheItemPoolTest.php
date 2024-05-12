<?php

use Borsch\Cache\CacheItem;
use Borsch\Cache\Exception\InvalidKeyException;
use Psr\Cache\CacheItemInterface;

test('getItem() returns valide item', function () {
    expect($this->pool->getItem('foo'))->toBeInstanceOf(CacheItemInterface::class)
        ->and($this->pool->getItem('foo')->get())->toBe('bar');
});

test('getItem() throws exception with invalid character key', function () {
    $this->pool->getItem('f?!@#o');
})->throws(InvalidKeyException::class);

test('getItems() returns valide items', function () {
    $items = $this->pool->getItems(['foo', 'bar']);

    expect($items)->toBeArray()->toHaveCount(2)
        ->and($items['foo']->get())->toBe('bar')
        ->and($items['bar']->get())->toBe('baz');
});

test('hasItem() returns true when has item', function () {
    expect($this->pool->hasItem('foo'))->toBeTrue();
});

test('hasItem() returns false when has not item', function () {
    expect($this->pool->hasItem('oof'))->toBeFalse();
});

test('hasItem() throws exception with invalid character key', function () {
    $this->pool->hasItem('f?!@#o');
})->throws(InvalidKeyException::class);

test('clear() removes all entries', function () {
    $this->pool->clear();
    expect($this->pool->hasItem('foo'))->toBeFalse()
        ->and($this->pool->hasItem('bar'))->toBeFalse();
});

test('deleteItem() removes an item', function () {
    expect($this->pool->hasItem('foo'))->toBeTrue();
    $this->pool->deleteItem('foo');
    expect($this->pool->hasItem('foo'))->toBeFalse();
});

test('deleteItem() throws exception with invalid character key', function () {
    $this->pool->deleteItem('f?!@#o');
})->throws(InvalidKeyException::class);

test('deleteItems() removes items', function () {
    expect($this->pool->hasItem('foo'))->toBeTrue()
        ->and($this->pool->hasItem('bar'))->toBeTrue()
        ->and($this->pool->deleteItems(['foo', 'bar']))->toBeTrue()
        ->and($this->pool->hasItem('foo'))->toBeFalse()
        ->and($this->pool->hasItem('bar'))->toBeFalse();

});

test('save() saves the item', function () {
    expect($this->pool->hasItem('demo'))->toBeFalse();
    $this->pool->save(new CacheItem('demo', 'val'));
    expect($this->pool->hasItem('demo'))->toBeTrue();
});

test('saveDeferred() saves the item', function () {
    expect($this->pool->hasItem('demo'))->toBeFalse();
    $this->pool->saveDeferred(new CacheItem('demo', 'val'));
    expect($this->pool->hasItem('demo'))->toBeTrue();
});

test('commit() returns true', function () {
    expect($this->pool->commit())->toBeTrue();
});
