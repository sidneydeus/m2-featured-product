<?php
declare(strict_types=1);

namespace Sidneydeus\FeaturedProduct\Observer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushFeaturedProductCache implements ObserverInterface
{
    private TypeListInterface $cacheTypeList;

    public function __construct(TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    public function execute(Observer $observer): void
    {
        $this->cacheTypeList->cleanType('full_page');
    }
}
