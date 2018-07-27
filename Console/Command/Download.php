<?php

namespace Spolnicki\StocksImport\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Spolnicki\StocksImport\Model\Config as StocksImportConfig;
use Spolnicki\StocksImport\Model\DataFileManagement;

class Download extends Command
{
    const INPUT_KEY_HOST = "host";
    const INPUT_KEY_USERNAME = "username";
    const INPUT_KEY_PASSWORD = "password";
    const INPUT_KEY_TIMEOUT = "timeout";
    const INPUT_KEY_PATH = "path";
    const INPUT_KEY_SAVE_AS = "save_as";

    /** @var StocksImportConfig */
    protected $stocksImportConfig;

    /** @var DataFileManagement */
    protected $dataFileManagement;

    public function __construct(
        StocksImportConfig $stocksImportConfig,
        DataFileManagement $stocksImportFileManagement,
        $name = null
    )
    {
        $this->stocksImportConfig = $stocksImportConfig;
        $this->dataFileManagement = $stocksImportFileManagement;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /**
             * Retrieve arguments
             */
            $host = $input->getOption(static::INPUT_KEY_HOST);
            $username = $input->getOption(static::INPUT_KEY_USERNAME);
            $password = $input->getOption(static::INPUT_KEY_PASSWORD);
            $timeout = $input->getOption(static::INPUT_KEY_TIMEOUT);
            $fileName = $input->getOption(static::INPUT_KEY_PATH);
            $saveAs = $input->getOption(static::INPUT_KEY_SAVE_AS);

            /**
             * Try to download file
             */
            $fileContent = $this->dataFileManagement->downloadFile($host, $username, $password, $timeout, $fileName);

            /**
             * Save the file
             */
            $pathToFile = $this->dataFileManagement->saveFile($fileContent, $saveAs);
            $output->writeln("File saved in $pathToFile");
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("stocksimport:download");
        $this->setDescription("Download file from SFTP");
        $this->setDefinition([
            new InputOption(
                self::INPUT_KEY_HOST,
                null,
                InputOption::VALUE_OPTIONAL,
                "Host",
                $this->stocksImportConfig->getSftpHost()
            ),
            new InputOption(
                self::INPUT_KEY_USERNAME,
                null,
                InputOption::VALUE_OPTIONAL,
                "Username",
                $this->stocksImportConfig->getSftpUsername()
            ),
            new InputOption(
                self::INPUT_KEY_PASSWORD,
                null,
                InputOption::VALUE_OPTIONAL,
                "Password",
                $this->stocksImportConfig->getSftpPassword()
            ),
            new InputOption(
                self::INPUT_KEY_TIMEOUT,
                null,
                InputOption::VALUE_OPTIONAL,
                "Connection timeout in seconds",
                $this->stocksImportConfig->getSftpTimeout()
            ),
            new InputOption(
                self::INPUT_KEY_PATH,
                null,
                InputOption::VALUE_OPTIONAL,
                "File name with path on server",
                $this->stocksImportConfig->getSftpPath()
            ),
            new InputOption(
                self::INPUT_KEY_SAVE_AS,
                null,
                InputOption::VALUE_OPTIONAL,
                "The name of the file on the local",
                $this->stocksImportConfig->getLocalFileName()
            )
        ]);
        parent::configure();
    }
}
