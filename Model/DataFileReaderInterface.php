<?php

namespace Spolnicki\StocksImport\Model;

use Magento\Framework\Filesystem\Directory\ReadInterface;

interface DataFileReaderInterface
{
    /**
     * Read file by name from given directory
     *
     * @param $fileName
     * @param ReadInterface $storageDirectory
     * @return DataFileReaderInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function readFile($fileName, ReadInterface $storageDirectory): DataFileReaderInterface;

    /**
     * Retrieve list of stocks items data as SKU => Qty
     *
     * @return array
     */
    public function getItems(): array;
} 