<?php
declare(strict_types=1);

namespace Sidneydeus\FeaturedProduct\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_ENABLED = 'featured_product/stock/enabled';
    public const XML_PATH_SKU = 'featured_product/stock/sku';
    public const XML_PATH_POLL_INTERVAL = 'featured_product/stock/poll_interval';

    private const MIN_POLL_INTERVAL = 1000;

    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Whether the featured product feature is enabled for the current store.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * SKU of the product to feature, trimmed; empty string when not configured.
     *
     * @return string
     */
    public function getSku(): string
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_SKU,
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Stock polling interval in milliseconds, never lower than MIN_POLL_INTERVAL.
     *
     * @return int
     */
    public function getPollInterval(): int
    {
        $interval = (int)$this->scopeConfig->getValue(
            self::XML_PATH_POLL_INTERVAL,
            ScopeInterface::SCOPE_STORE
        );

        return $interval >= self::MIN_POLL_INTERVAL ? $interval : self::MIN_POLL_INTERVAL;
    }
}
