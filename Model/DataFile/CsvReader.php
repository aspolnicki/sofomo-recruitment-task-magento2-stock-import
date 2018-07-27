<?php

namespace Spolnicki\StocksImport\Model\DataFile;

use Magento\Framework\Filesystem\Directory\ReadInterface;
use Spolnicki\StocksImport\Model\DataFileReaderInterface;

class CsvReader extends AbstractReader
{
    const COLUMN_ORDER_STOCK_ID = 0; // Stock id (manufacturer internal)
    const COLUMN_ORDER_SKU = 1; // Product SKU
    const COLUMN_ORDER_QTY = 2; // Quantity
    const COLUMN_ORDER_WAREHOUSE_ID = 3; // Warehouse id (where product is placed)

    /** @var array */
    protected $items = [];

    /**
     * {@inheritdoc}
     */
    public function readFile($fileName, ReadInterface $storageDirectory = null): DataFileReaderInterface
    {
        parent::readFile($fileName, $storageDirectory);
        if ($this->stream !== FALSE) {
            while (($data = fgetcsv($this->stream, 1000, ",")) !== FALSE) {
                $sku = (string)$data[static::COLUMN_ORDER_SKU];
                $qty = (float)$data[static::COLUMN_ORDER_QTY];
                if (isset($this->items[$sku])) {
                    $this->items[$sku] += $qty;
                } else {
                    $this->items[$sku] = $qty;
                }
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->items;
    }
}