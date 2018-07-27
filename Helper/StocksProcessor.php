<?php

namespace Spolnicki\StocksImport\Helper;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Spolnicki\StocksImport\Model\DataFileManagement;

class StocksProcessor
{
    /** @var DataFileManagement */
    protected $dataFileManagement;

    /** @var ProductResource */
    protected $productResource;

    /** @var StockItemCriteriaInterfaceFactory */
    protected $stockItemCriteriaFactory;

    /** @var StockItemRepositoryInterface */
    protected $stockItemRepository;

    public function __construct(
        DataFileManagement $stocksImportFileManagement,
        ProductResource $productResource,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepositoryInterface $stockItemRepository
    )
    {
        $this->dataFileManagement = $stocksImportFileManagement;
        $this->productResource = $productResource;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param array $skus
     * @return array SKU => ID
     */
    public function getProductIdsBySkus(array $skus): array
    {
        return $this->productResource->getProductsIdsBySkus($skus);
    }

    /**
     * @param array $productIds
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface[]
     */
    public function getStockItemsByProductIds(array $productIds): array
    {
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($productIds);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $stockItems = $stockItemCollection->getItems();
        return $stockItems;
    }

    /**
     * Prepare stocks data for further processing
     *
     * @param string $fileName
     * @param ReadInterface $storageDirectory
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getStocksData(string $fileName, ReadInterface $storageDirectory): array
    {
        $reader = $this->dataFileManagement->getReader();
        $reader->readFile($fileName, $storageDirectory);
        $stocks = $reader->getItems();
        return $stocks;
    }
}