<?php
declare(strict_types=1);

namespace Sidneydeus\FeaturedProduct\CustomerData;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Sidneydeus\FeaturedProduct\Model\Config;

class FeaturedProductStock implements SectionSourceInterface
{
    private ProductRepositoryInterface $productRepository;
    private StockStateInterface $stockState;
    private Config $config;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StockStateInterface $stockState
     * @param Config $config
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockStateInterface $stockState,
        Config $config
    ) {
        $this->productRepository = $productRepository;
        $this->stockState = $stockState;
        $this->config = $config;
    }

    /**
     * Build the customer-data payload for the featured product stock section.
     *
     * Returns an empty payload when the feature is disabled or the configured
     * product cannot be found.
     *
     * @return array{product_id: int|null, qty: int, is_in_stock: bool, stock_status: string}
     */
    public function getSectionData(): array
    {
        if (!$this->config->isEnabled()) {
            return $this->emptySectionData();
        }

        try {
            $product = $this->productRepository->get($this->config->getSku());
            $websiteId = (int)$product->getStore()->getWebsiteId();
            $qty = (int)$this->stockState->getStockQty($product->getId(), $websiteId);
            $isInStock = (bool)$this->stockState->verifyStock($product->getId(), $websiteId);

            return [
                'product_id' => $product->getId(),
                'qty' => $qty,
                'is_in_stock' => $isInStock,
                'stock_status' => $isInStock ? 'in_stock' : 'out_of_stock',
            ];
        } catch (NoSuchEntityException $e) {
            return $this->emptySectionData();
        }
    }

    /**
     * Default payload used when no stock information is available.
     *
     * @return array{product_id: null, qty: int, is_in_stock: bool, stock_status: string}
     */
    private function emptySectionData(): array
    {
        return [
            'product_id' => null,
            'qty' => 0,
            'is_in_stock' => false,
            'stock_status' => 'out_of_stock',
        ];
    }
}
