<?php declare(strict_types = 1);

namespace Borsch\Cache;

use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{

    public function __construct(
        protected string $key,
        protected mixed $value = null,
        protected null|int|DateTimeInterface $expiration = null
    ) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        if ($this->expiration instanceof \DateTimeInterface) {
            return $this->expiration->getTimestamp() > time();
        } elseif (is_int($this->expiration)) {
            return $this->expiration > time();
        }

        // $this->expiration === null
        return true;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        $this->expiration = match (true) {
            is_int($time) => time() + $time,
            $time instanceof \DateInterval => (new \DateTime())->add($time),
            default => null
        };

        return $this;
    }
}
