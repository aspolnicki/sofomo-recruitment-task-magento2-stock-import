<?php

namespace Spolnicki\StocksImport\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Spolnicki\StocksImport\Model\Config as StocksImportConfig;

class DataFileManagement
{
    /** @var StocksImportConfig */
    protected $stocksImportConfig;

    /** @var Sftp */
    protected $sftp;

    public function __construct(
        StocksImportConfig $stocksImportConfig,
        Sftp $sftp
    )
    {
        $this->stocksImportConfig = $stocksImportConfig;
        $this->sftp = $sftp;
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $timeout
     * @param string $fileName Path to file with file name
     * @return string
     * @throws \Exception
     */
    public function downloadFile($host, $username, $password, $timeout, $fileName): string
    {
        $this->sftp->open([
            "host" => $host,
            "username" => $username,
            "password" => $password,
            "timeout" => $timeout
        ]);

        $fileContent = $this->sftp->read($fileName);
        if (false === $fileContent) {
            throw new \Exception("Unable to download a file from SFTP server");
        }
        return (string)$fileContent;
    }

    /**
     * @param $fileContent
     * @param $fileName
     * @param WriteInterface|null $storageDirectory
     * @return string Absolute path to file
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function saveFile($fileContent, $fileName, WriteInterface $storageDirectory = null): string
    {
        if (is_null($storageDirectory)) {
            $storageDirectory = $this->stocksImportConfig->getStorageDirectory();
        }
        $storageDirectory->writeFile($fileName, $fileContent);
        return $storageDirectory->getAbsolutePath($fileName);
    }

    /**
     * @param null $readerType
     * @return mixed
     * @throws NotFoundException
     */
    public function getReader($readerType = null): DataFileReaderInterface
    {
        if (is_null($readerType)) {
            $readerType = $this->stocksImportConfig->getReaderType();
        }
        $readers = $this->stocksImportConfig->getReaders();
        if (isset($readers[$readerType]['reader'])) {
            return $readers[$readerType]['reader'];
        } else {
            throw new NotFoundException(__("Unsupported file type: %1", $readerType));
        }
    }
}