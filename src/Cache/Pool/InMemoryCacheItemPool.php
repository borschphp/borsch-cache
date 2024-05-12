<?php

namespace Borsch\Cache\Pool;

use Borsch\Cache\CacheItem;
use Borsch\Cache\Trait\HasKeyValidation;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class InMemoryCacheItemPool implements CacheItemPoolInterface
{

    use HasKeyValidation;

    protected array $items = [];

    public function getItem(string $key): CacheItemInterface
    {
        $this->validateKey($key);

        return $this->items[$key] ?? new CacheItem($key);
    }

    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        $this->validateKey($key);

        return isset($this->items[$key]);
    }

    public function clear(): bool
    {
        $this->items = [];

        return true;
    }

    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);

        unset($this->items[$key]);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    public function commit(): bool
    {
        return true;
    }
}
