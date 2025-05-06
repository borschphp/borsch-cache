<?php declare(strict_types=1);

namespace Borsch\Cache\Pool;

use Borsch\Cache\CacheItem;
use Borsch\Cache\Trait\HasKeyValidation;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ArrayCacheItemPool implements CacheItemPoolInterface
{

    use HasKeyValidation;

    /** @var ArrayCollection<string, CacheItemInterface> */
    protected ArrayCollection $items;

    /** @var ArrayCollection<string, CacheItemInterface> */
    protected ArrayCollection $deferred;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->deferred = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key): CacheItemInterface
    {
        $this->validateKey($key);

        if ($this->items->containsKey($key)) {
            /** @var CacheItemInterface $item */
            $item = $this->items->get($key);

            return $item;
        }

        return new CacheItem($key, null);
    }

    /**
     * @inheritDoc
     * @return array<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        $this->validateKey($key);

        return $this->items->containsKey($key) ||
            $this->deferred->containsKey($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->items->clear();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);

        if ($this->items->containsKey($key)) {
            $this->items->remove($key);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        return array_reduce(
            $keys,
            fn (bool $carry, string $key) => $carry && $this->deleteItem($key),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->items[$item->getKey()] = $item;
        }

        $this->deferred->clear();

        return true;
    }
}
