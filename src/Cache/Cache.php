<?php

namespace Borsch\Cache;

use Borsch\Cache\Logger\VoidLogger;
use Borsch\Cache\Trait\HasKeyValidation;
use DateInterval;
use DateTime;
use Exception;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use function array_map, array_combine, array_fill, count;

class Cache implements CacheInterface, LoggerAwareInterface
{

    use HasKeyValidation;

    public function __construct(
        protected CacheItemPoolInterface $pool,
        protected LoggerInterface $logger = new VoidLogger()
    ) {}

    protected function tryGetValue(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (Exception $exception) {
            if ($exception instanceof CacheException) {
                throw $exception;
            }

            $this->logger->alert('An error occurred with cache: {message}', [
                'message' => $exception->getMessage()
            ]);
        }

        return $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);

        return $this->tryGetValue(function () use ($key, $default) {
            if ($this->pool->hasItem($key)) {
                $item = $this->pool->getItem($key);

                return $item->isHit() ? $item->get() : $default;
            }

            return $default;
        }, $default);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);

        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime())->add($ttl);
        }

        return $this->tryGetValue(
            fn() => $this->pool->save(new CacheItem($key, $value, $ttl)),
            false
        );
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);

        return $this->tryGetValue(
            fn() => $this->pool->deleteItem($key),
            false
        );
    }

    public function clear(): bool
    {
        return $this->tryGetValue(
            fn() => $this->pool->clear(),
            false
        );
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->tryGetValue(
            fn() => array_map(
                fn(CacheItemInterface $item) => $item->isHit() ? $item->get() : $default,
                (array)$this->pool->getItems((array)$keys)
            ),
            array_combine(
                (array)$keys,
                array_fill(0, count((array)$keys), $default)
            )
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
        $this->validateKey($key);

        return $this->pool->hasItem($key);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
