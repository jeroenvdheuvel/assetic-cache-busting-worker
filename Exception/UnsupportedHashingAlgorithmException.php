<?php

namespace jvdh\AsseticWorker\Exception;

use RuntimeException;

class UnsupportedHashingAlgorithmException extends RuntimeException
{
    /**
     * @param string $algorithm
     */
    public function __construct($algorithm)
    {
        $message = sprintf(
            '%s is an unsupported hashing algorithm. Supported algorithms are [%s]',
            $algorithm,
            implode(', ', hash_algos())
        );
        parent::__construct($message);
    }
}