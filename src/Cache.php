<?php declare(strict_types=1);

namespace Borsch\Cache;

use Borsch\Cache\Trait\HasKeyValidation;
use DateInterval;
use Psr\Cache\{CacheItemPoolInterface};
use DateTime;
use Psr\Log\{LoggerAwareInterface, LoggerInterface, NullLogger};
use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface, LoggerAwareInterface
{

    use HasKeyValidation;

    public function __construct(
        protected CacheItemPoolInterface $pool,
        protected LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->pool->getItem($key);
        if (!$item->isHit()) {
            return $default;
        }

        return $item->get();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->get($key, $default);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime())->add($ttl);
        }

        return $this->pool->save(new CacheItem($key, $value, $ttl));
    }

    /**
     * @inheritDoc
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $this->logger->error(sprintf('Unable to set cache with key "%s"', $key));
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->pool->clear();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $this->logger->error(sprintf('Unable to delete cache with key "%s"', $key));
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
