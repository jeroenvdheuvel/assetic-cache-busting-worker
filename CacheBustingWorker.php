<?php

namespace jvdh\AsseticWorker;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\WorkerInterface;
use jvdh\AsseticWorker\Exception\UnsupportedHashingAlgorithmException;

class CacheBustingWorker implements WorkerInterface
{
    /**
     * @var string
     */
    private $separator;

    /**
     * @var int
     */
    private $hashLength;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @param string $separator
     * @param int $hashLength
     * @param string $algorithm
     */
    public function __construct($separator = '-', $hashLength = 8, $algorithm = 'sha1')
    {
        $this->ensureHashAlgorithmIsValidOrThrowException($algorithm);

        $this->separator = $separator;
        $this->hashLength = $hashLength;
        $this->algorithm = $algorithm;
    }

    /**
     * @inheritdoc
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        if (!$path = $asset->getTargetPath()) {
            // no path to work with
            return;
        }

        if (!$search = pathinfo($path, PATHINFO_EXTENSION)) {
            // nothing to replace
            return;
        }

        $replace = $this->separator.$this->getHash($asset).'.'.$search;
        if (preg_match('/'.preg_quote($replace, '/').'$/', $path)) {
            // already replaced
            return;
        }

        $asset->setTargetPath(
            preg_replace('/\.'.preg_quote($search, '/').'$/', $replace, $path)
        );
    }

    /**
     * @param AssetInterface $asset
     * @return string
     */
    private function getHash(AssetInterface $asset)
    {
        return substr(hash($this->algorithm, $asset->dump()), 0, $this->hashLength);
    }

    /**
     * @param string $algorithm
     */
    private function ensureHashAlgorithmIsValidOrThrowException($algorithm)
    {
        if (!in_array($algorithm, hash_algos(), true)) {
            throw new UnsupportedHashingAlgorithmException($algorithm);
        }
    }
}
