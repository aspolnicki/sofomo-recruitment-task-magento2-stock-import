<?php

namespace Spolnicki\StocksImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class Config
{
    /** CRON config */
    const PATH_CRON_ENABLED = "stockimport/cron/enabled";
    const PATH_CRON_TIME_EXPR = "stockimport/cron/time_expr";

    /** SFTP config */
    const PATH_SFTP_HOST = "stockimport/sftp/host";
    const PATH_SFTP_USERNAME = "stockimport/sftp/username";
    const PATH_SFTP_PASSWORD = "stockimport/sftp/password";
    const PATH_SFTP_TIMEOUT = "stockimport/sftp/timeout";
    const PATH_SFTP_PATH = "stockimport/sftp/path";

    /** General config */
    const PATH_GENERAL_READER_TYPE = "stockimport/general/reader_type";
    const PATH_GENERAL_LOCAL_FILE_NAME = "stockimport/general/local_file_name";
    const PATH_GENERAL_BATCH_SIZE = "stockimport/general/batch_size";

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var EncryptorInterface */
    protected $encryptor;

    /** @var array */
    protected $readers;

    /** Filesystem */
    protected $filesystem;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Filesystem $filesystem,
        array $readers = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->readers = $readers;
        $this->filesystem = $filesystem;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }

    /**
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return (bool)$this->getConfigValue(static::PATH_CRON_ENABLED);
    }

    /**
     * @return string
     */
    public function getCronTimeExpr(): string
    {
        return (string)$this->getConfigValue(static::PATH_CRON_TIME_EXPR);
    }

    /**
     * @return string
     */
    public function getSftpHost(): string
    {
        return (string)$this->getConfigValue(static::PATH_SFTP_HOST);
    }

    /**
     * @return string
     */
    public function getSftpUsername(): string
    {
        return (string)$this->getConfigValue(static::PATH_SFTP_USERNAME);
    }

    /**
     * @return string
     */
    public function getSftpPasswordRaw(): string
    {
        return (string)$this->getConfigValue(static::PATH_SFTP_PASSWORD);
    }

    /**
     * @return string
     */
    public function getSftpPassword(): string
    {
        return (string)$this->encryptor->decrypt($this->getSftpPasswordRaw());
    }

    /**
     * @return integer
     */
    public function getSftpTimeout(): int
    {
        return (int)$this->getConfigValue(static::PATH_SFTP_TIMEOUT);
    }

    /**
     * @return string
     */
    public function getSftpPath(): string
    {
        return (string)$this->getConfigValue(static::PATH_SFTP_PATH);
    }

    /**
     * @return string
     */
    public function getReaderType(): string
    {
        return (string)$this->getConfigValue(static::PATH_GENERAL_READER_TYPE);
    }

    /**
     * @return array
     */
    public function getReaders(): array
    {
        return $this->readers;
    }

    /**
     * @return string
     */
    public function getLocalFileName(): string
    {
        return (string)$this->getConfigValue(static::PATH_GENERAL_LOCAL_FILE_NAME);
    }

    /**
     * @return int
     */
    public function getBatchSize(): int
    {
        return (int)$this->getConfigValue(static::PATH_GENERAL_BATCH_SIZE);
    }

    /**
     * @return WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getStorageDirectory(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
    }
}