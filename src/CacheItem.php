<?php declare(strict_types = 1);

namespace Borsch\Cache;

use DateInterval;
use DateTime;
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
        if ($this->isHit()) {
            return $this->value;
        }

        return null;
    }

    public function isHit(): bool
    {
        if ($this->value === null) {
            return false;
        }

        if ($this->expiration instanceof DateTimeInterface) {
            return $this->expiration->getTimestamp() >= time();
        }

        if (is_int($this->expiration)) {
            return $this->expiration >= time();
        }

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
            $time instanceof DateInterval => (new DateTime())->add($time),
            default => null
        };

        return $this;
    }
}
