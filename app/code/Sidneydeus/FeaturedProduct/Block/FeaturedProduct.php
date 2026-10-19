<?php
declare(strict_types=1);

namespace Sidneydeus\FeaturedProduct\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Sidneydeus\FeaturedProduct\Model\Config;

class FeaturedProduct extends Template
{
    private ProductRepositoryInterface $productRepository;
    private ImageBuilder $imageBuilder;
    private Config $config;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ImageBuilder $imageBuilder
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
        $this->config = $config;
    }

    /**
     * Whether the featured product feature is enabled in the store configuration.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Stock polling interval (in milliseconds) configured for the frontend observer.
     *
     * @return int
     */
    public function getPollInterval(): int
    {
        return $this->config->getPollInterval();
    }

    /**
     * Load the configured featured product by its SKU.
     *
     * @return Product|null Null when the product does not exist.
     */
    public function getFeaturedProduct(): ?Product
    {
        try {
            $product = $this->productRepository->get($this->config->getSku());
            if (!$product->hasData('price') && $product->getId()) {
                $product = $this->productRepository->getById($product->getId());
            }
            return $product;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Build the product image block using the configured image role.
     *
     * @param Product $product
     * @param string $imageId
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage(Product $product, string $imageId = 'product_page_image_medium')
    {
        return $this->imageBuilder->create($product, $imageId);
    }

    /**
     * Render the final price HTML for the given product.
     *
     * @param Product $product
     * @return string Empty string when the price render block is unavailable.
     */
    public function getProductPrice(Product $product): string
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            return '';
        }

        return $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            [
                'price_render' => 'product.price.render.default',
                'price_type_code' => FinalPrice::PRICE_CODE,
                'zone' => Render::ZONE_ITEM_LIST,
                'item' => $product,
            ]
        );
    }
}
