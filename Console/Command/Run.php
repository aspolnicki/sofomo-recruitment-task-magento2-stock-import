<?php

namespace Spolnicki\StocksImport\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Spolnicki\StocksImport\Model\Config as StocksImportConfig;
use Spolnicki\StocksImport\Model\StocksProcessor;
use Spolnicki\StocksImport\Helper\StocksProcessor as StocksProcessorHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{
    const INPUT_KEY_FILE_NAME = "file";
    const INPUT_KEY_BATCH = "batch";

    /** @var StocksImportConfig */
    protected $stocksImportConfig;

    /** @var StocksProcessorHelper */
    protected $stocksProcessorHelper;

    /** @var StocksProcessor */
    protected $stocksProcessor;

    /** @var State * */
    protected $state;

    public function __construct(
        StocksImportConfig $stocksImportConfig,
        StocksProcessorHelper $stocksProcessorHelper,
        StocksProcessor $stocksProcessor,
        State $state,
        $name = null
    )
    {
        $this->stocksImportConfig = $stocksImportConfig;
        $this->stocksProcessorHelper = $stocksProcessorHelper;
        $this->stocksProcessor = $stocksProcessor;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /**
             * Why? StockItem use customer session
             * @see \Magento\CatalogInventory\Model\Stock\Item
             */
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            /**
             * Retrieve arguments
             */
            $fileName = $input->getOption(static::INPUT_KEY_FILE_NAME);
            $storageDirectory = $this->stocksImportConfig->getStorageDirectory();
            $batchSize = $input->getOption(static::INPUT_KEY_BATCH);

            /**
             * Prepare data for process
             */
            $stocks = $this->stocksProcessorHelper->getStocksData($fileName, $storageDirectory);

            /**
             * Process data
             */
            $this->stocksProcessor->process($stocks, $batchSize, $output);

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
        $this->setName("stocksimport:run");
        $this->setDescription("Run stock import process");
        $this->setDefinition([
            new InputOption(
                self::INPUT_KEY_FILE_NAME,
                "f",
                InputOption::VALUE_OPTIONAL,
                "File name of stock data",
                $this->stocksImportConfig->getLocalFileName()
            ),
            new InputOption(
                self::INPUT_KEY_BATCH,
                "b",
                InputOption::VALUE_OPTIONAL,
                "Batch size to process rows",
                $this->stocksImportConfig->getBatchSize()
            )
        ]);
        parent::configure();
    }
}
