<?php

namespace jvdh\Tests\AsseticWorker\CacheBustingWorker\Exception;

use jvdh\AsseticWorker\Exception\UnsupportedHashingAlgorithmException;
use PHPUnit_Framework_TestCase;

class UnsupportedHashingAlgorithmTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getUnsupportedAlgorithmData
     *
     * @param string $algorithm
     */
    public function testGetMessage($algorithm)
    {
        $e = new UnsupportedHashingAlgorithmException($algorithm);

        $this->assertStringStartsWith($algorithm . ' is an unsupported hashing algorithm.', $e->getMessage());
        $this->assertStringEndsWith(
            sprintf('Supported algorithms are [%s]', implode(', ', hash_algos())), $e->getMessage()
        );
    }

    /**
     * @return array
     */
    public function getUnsupportedAlgorithmData()
    {
        return array(
            array('something'),
            array('something else'),
        );
    }
}
