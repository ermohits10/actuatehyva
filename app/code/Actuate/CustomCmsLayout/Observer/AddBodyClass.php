<?php
namespace Actuate\CustomCmsLayout\Observer;
use Magento\Framework\View\Page\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class AddBodyClass implements ObserverInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function execute(EventObserver $observer)
    {
        $layout = $this->config->getPageLayout();
        if ($layout == "custom-cms" || $layout == "custom-category-home-cinema") {
            $this->config->addBodyClass("page-layout-2columns-left");
        }
    }
}