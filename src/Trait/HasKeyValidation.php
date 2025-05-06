<?php declare(strict_types=1);

namespace Borsch\Cache\Trait;

use Borsch\Cache\Exception\InvalidKeyException;

trait HasKeyValidation
{

    /**
     * @throws InvalidKeyException
     */
    protected function validateKey(string $key): void
    {
        if (strlen($key) == 0) {
            throw InvalidKeyException::emptyString();
        }

        if (strlen($key) > 64) {
            throw InvalidKeyException::tooLongString($key);
        }

        if (!preg_match('/^[A-Za-z0-9_.\-]+$/', $key)) {
            throw InvalidKeyException::nonAlphanumericChars($key);
        }
    }
}