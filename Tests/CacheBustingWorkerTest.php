<?php

namespace jvdh\Tests\AsseticWorker\CacheBustingWorker;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Assetic\Factory\AssetFactory;
use jvdh\AsseticWorker\CacheBustingWorker;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class CacheBustingWorkerTest extends PHPUnit_Framework_TestCase
{
    public function testProcess_withFileAsset_shouldHash()
    {
        $asset = $this->getFileAsset('asset.txt');

        $this->getCacheBustingWorker()->process($asset, $this->getAssetFactory());

        $this->assertSame('asset-05fac943.txt', $asset->getTargetPath());
    }

    public function testProcess_withAssetCollection_shouldHash()
    {
        $collection = new AssetCollection();
        $collection->add($this->getFileAsset('asset.txt'));
        $collection->add($this->getStringAsset('string', 'string.txt'));
        $collection->setTargetPath('collection.txt');

        $this->getCacheBustingWorker()->process($collection, $this->getAssetFactory());

        $this->assertSame('collection-ae851400.txt', $collection->getTargetPath());
    }

    public function testProcess_calledTwice_shouldHashOnlyOnce()
    {
        $worker = $this->getCacheBustingWorker();
        $factory = $this->getAssetFactory();

        $asset = new StringAsset('this string will be hashed');
        $asset->setTargetPath('asset.txt');
        $worker->process($asset, $factory);
        $this->assertSame('asset-a2eea71a.txt', $asset->getTargetPath());

        $worker->process($asset, $factory);
        $this->assertSame('asset-a2eea71a.txt', $asset->getTargetPath());
    }

    /**
     * @dataProvider getHashData
     *
     * @param $separator
     * @param $hashLength
     * @param $algorithm
     * @param $content
     * @param $filename
     * @param $expectedFilename
     */
    public function testProcess_withStringAsset_shouldHash($separator, $hashLength, $algorithm)
    {
        $filename = 'asset.txt';
        $content = 'asset content';
        $expectedFilename = 'asset' . $separator . $this->getShortenedHash($algorithm, $content, $hashLength) . '.txt';

        $asset = $this->getStringAsset($content, $filename);
        $this->getCacheBustingWorker($separator, $hashLength, $algorithm)->process($asset, $this->getAssetFactory());

        $this->assertSame($expectedFilename, $asset->getTargetPath());
    }

    /**
     * @return array
     */
    public function getHashData()
    {
        return array(
            array('-', 7, 'sha1'),
            array('+', 10, 'md5'),
        );
    }

    /**
     * @param string $algorithm
     * @param string $content
     * @param int $length
     * @return string
     */
    private function getShortenedHash($algorithm, $content, $length)
    {
        return substr(hash($algorithm, $content), 0, $length);
    }

    public function testProcess_withoutTarget_shouldNotChangeTarget()
    {
        $asset = new StringAsset('this string will be hashed');

        $this->getCacheBustingWorker()->process($asset, $this->getAssetFactory());

        $this->assertNull($asset->getTargetPath());
    }

    public function testProcess_withAssetWithoutExtension_shouldNotChangeTarget()
    {
        $targetPath = 'target-path-without-file-extension';
        $asset = new StringAsset('this string will be hashed');
        $asset->setTargetPath($targetPath);

        $this->getCacheBustingWorker()->process($asset, $this->getAssetFactory());

        $this->assertSame($targetPath, $asset->getTargetPath());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The source file "non-existing-asset.txt" does not exist.
     */
    public function testProcess_withNonExistingFile_shouldThrowException()
    {
        $asset = new FileAsset('non-existing-asset.txt');
        $asset->setTargetPath('non-existing-asset.txt');

        $this->getCacheBustingWorker()->process($asset, $this->getAssetFactory());
    }

    /**
     * @expectedException \jvdh\AsseticWorker\Exception\UnsupportedHashingAlgorithm
     */
    public function testProcess_withUnsupportedHashAlgorithm_shouldThrowException()
    {
        $worker = new CacheBustingWorker('-', 7, 'unknown');
        $worker->process($this->getStringAsset('string', 'out.txt'), $this->getAssetFactory());
    }

    /**
     * @param string $content
     * @param string $filename
     * @return StringAsset
     */
    private function getStringAsset($content, $filename)
    {
        $asset = new StringAsset($content);
        $asset->setTargetPath($filename);

        return $asset;
    }

    /**
     * @param string $filename
     * @return FileAsset
     */
    private function getFileAsset($filename)
    {
        $asset = new FileAsset($this->getAssetPath() . $filename);
        $asset->setTargetPath($filename);

        return $asset;
    }

    /**
     * @return string
     */
    private function getAssetPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $seperator
     * @param int $hashLength
     * @param string $algorithm
     * @return CacheBustingWorker
     */
    private function getCacheBustingWorker($seperator = '-', $hashLength = 8, $algorithm = 'sha1')
    {
        return new CacheBustingWorker($seperator, $hashLength, $algorithm);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|AssetFactory
     */
    private function getAssetFactory()
    {
        return $this->getMockBuilder('Assetic\Factory\AssetFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
