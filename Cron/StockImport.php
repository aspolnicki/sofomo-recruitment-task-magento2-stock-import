<?php

namespace Spolnicki\StocksImport\Cron;

use Psr\Log\LoggerInterface;
use Spolnicki\StocksImport\Model\Config as StocksImportConfig;
use Spolnicki\StocksImport\Model\DataFileManagement;
use Spolnicki\StocksImport\Model\StocksProcessor;
use Spolnicki\StocksImport\Helper\StocksProcessor as StocksProcessorHelper;

class StockImport
{
    /** @var StocksImportConfig */
    protected $stocksImportConfig;

    /** @var StocksProcessorHelper */
    protected $stocksProcessorHelper;

    /** @var StocksProcessor */
    protected $stocksProcessor;

    /** @var DataFileManagement */
    protected $dataFileManagement;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        StocksImportConfig $stocksImportConfig,
        StocksProcessorHelper $stocksProcessorHelper,
        StocksProcessor $stocksProcessor,
        DataFileManagement $stocksImportFileManagement,
        LoggerInterface $logger
    )
    {
        $this->stocksImportConfig = $stocksImportConfig;
        $this->stocksProcessorHelper = $stocksProcessorHelper;
        $this->stocksProcessor = $stocksProcessor;
        $this->dataFileManagement = $stocksImportFileManagement;
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if ($this->stocksImportConfig->isCronEnabled()) {
            try {
                /**
                 * Retrieve arguments for download a file and processing
                 */
                $host = $this->stocksImportConfig->getSftpHost();
                $username = $this->stocksImportConfig->getSftpUsername();
                $password = $this->stocksImportConfig->getSftpPassword();
                $timeout = $this->stocksImportConfig->getSftpTimeout();
                $fileNameSftp = $this->stocksImportConfig->getSftpPath();
                $fileName = $this->stocksImportConfig->getLocalFileName();
                $batchSize = $this->stocksImportConfig->getBatchSize();
                $storageDirectory = $this->stocksImportConfig->getStorageDirectory();

                /**
                 * Try to download file
                 */
                $fileContent = $this->dataFileManagement->downloadFile($host, $username, $password, $timeout, $fileNameSftp);

                /**
                 * Save the file
                 */
                $this->dataFileManagement->saveFile($fileContent, $fileName);

                /**
                 * Prepare data for process
                 */
                $stocks = $this->stocksProcessorHelper->getStocksData($fileName, $storageDirectory);

                /**
                 * Process data
                 */
                $this->stocksProcessor->process($stocks, $batchSize, $this->logger);
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
            }
        }
    }
}
