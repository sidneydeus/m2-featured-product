<?php
declare(strict_types=1);

namespace Sidneydeus\FeaturedProduct\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class FeaturedProduct extends Template
{
    private ProductRepositoryInterface $productRepository;
    private ImageBuilder $imageBuilder;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
    }

    public function getFeaturedProduct(): ?Product
    {
        try {
            $product = $this->productRepository->get('Produto XYZ');
            if (!$product->hasData('price') && $product->getId()) {
                $product = $this->productRepository->getById($product->getId());
            }
            return $product;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    public function getImage(Product $product, string $imageId = 'product_page_image_medium')
    {
        return $this->imageBuilder->create($product, $imageId);
    }

    public function getProductPrice(Product $product): string
    {
        return $this->getLayout()->createBlock(Render::class)
            ->setData('price_render', 'product.price.render.default')
            ->setData('price_type_code', FinalPrice::PRICE_CODE)
            ->setData('zone', Render::ZONE_ITEM_LIST)
            ->setData('item', $product)
            ->toHtml();
    }
}