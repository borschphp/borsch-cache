<?php

namespace Borsch\Cache\Exception;

use Exception;
use Psr\Cache\InvalidArgumentException as CacheInvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class InvalidKeyException extends Exception implements CacheInvalidArgumentException, SimpleCacheInvalidArgumentException
{

    public static function emptyString(): static
    {
        return new static('The key must be a non-empty string.');
    }

    public static function tooLongString(string $key): static
    {
        return new static(sprintf(
            'The key "%s" must have a length of up to 64 characters, %d given.',
            $key,
            strlen($key)
        ));
    }

    public static function nonAlphanumericChars(string $key): static
    {
        return new static(sprintf(
            'The key "%s" must be a string containing only alphanumeric characters, underscores, and dots.',
            $key
        ));
    }
}
