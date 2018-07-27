<?php

namespace Spolnicki\StocksImport\Model;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Spolnicki\StocksImport\Helper\StocksProcessor as StocksProcessorHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class StocksProcessor
{
    /** @var StocksProcessorHelper */
    protected $stocksProcessorHelper;

    /** @var StockItemRepositoryInterface */
    protected $stockItemRepository;

    public function __construct(
        StocksProcessorHelper $stocksProcessorHelper,
        StockItemRepositoryInterface $stockItemRepository
    )
    {
        $this->stocksProcessorHelper = $stocksProcessorHelper;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param array $stocks
     * @param int $batchSize
     * @param null $output
     * @return StocksProcessor
     */
    public function process(array $stocks, int $batchSize, $output = null): StocksProcessor
    {
        /** Prepare batch */
        $stocks = array_chunk($stocks, $batchSize, true);
        $parts = count($stocks);


        foreach ($stocks as $key => $batch) {
            $part = $key + 1;
            $this->write("Processing part $part/$parts", $output);

            /**
             * Prepare data for better performance
             * (loading collection is faster than loading one by one)
             */
            $productIds = $this->stocksProcessorHelper->getProductIdsBySkus(array_keys($batch));
            $stockItems = $this->stocksProcessorHelper->getStockItemsByProductIds($productIds);

            foreach ($stockItems as $stockItem) {
                $productId = $stockItem->getProductId();
                $sku = array_search($productId, $productIds);
                $qty = $batch[$sku];

                /**
                 * Setup stock quantities for stock item corresponding to product
                 */
                $stockItem->setQty($qty);
                $stockItem->setIsInStock(0 < $qty);

                try {
                    $this->stockItemRepository->save($stockItem);
                } catch (\Exception $exception) {
                    $this->write(
                        "Error for product ID: $productId (sku: $sku, qty: $qty) " . $exception->getMessage(),
                        $output
                    );
                }
            }
        }
        $this->write("Processing completed", $output);
        return $this;
    }

    /**
     * @param $message
     * @param $output
     * @return StocksProcessor
     */
    protected function write($message, $output): StocksProcessor
    {
        if ($output instanceof OutputInterface) {
            $output->writeln($message);
        }
        if ($output instanceof LoggerInterface) {
            $output->info($message);
        }
        return $this;
    }
}