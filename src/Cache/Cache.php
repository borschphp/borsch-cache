<?php

namespace Borsch\Cache;

use Borsch\Cache\Logger\VoidLogger;
use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use function array_map;

class Cache implements CacheInterface, LoggerAwareInterface
{

    public function __construct(
        protected CacheItemPoolInterface $pool,
        protected LoggerInterface $logger = new VoidLogger()
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->pool->hasItem($key)) {
            $item = $this->pool->getItem($key);
            return $item->isHit() ? $item->get() : $default;
        }

        return $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->pool->save(new CacheItem($key, $value, $ttl));
    }

    public function delete(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }

    public function clear(): bool
    {
        return $this->pool->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $items = $this->pool->getItems((array)$keys);

        return array_map(
            fn(CacheItemInterface $item) => $item->isHit() ?
                $item->get() : $default,
            (array)$items
        );
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            $success &= $this->set($key, $value, $ttl);
        }

        return $success;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            $success &= $this->delete($key);
        }

        return $success;
    }

    public function has(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
