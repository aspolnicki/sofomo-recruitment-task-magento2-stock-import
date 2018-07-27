<?php

namespace Spolnicki\StocksImport\Model\DataFile;

use Magento\Framework\Filesystem\Directory\ReadInterface;
use Spolnicki\StocksImport\Model\DataFileReaderInterface;

abstract class AbstractReader implements DataFileReaderInterface
{
    /** @var resource */
    protected $stream;

    /**
     * {@inheritdoc}
     */
    public function readFile($fileName, ReadInterface $storageDirectory): DataFileReaderInterface
    {
        $this->stream = fopen('php://memory', 'r+');
        $content = $storageDirectory->readFile($fileName);
        fwrite($this->stream, $content);
        rewind($this->stream);
        return $this;
    }
}