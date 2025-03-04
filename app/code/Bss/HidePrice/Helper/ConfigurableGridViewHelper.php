<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_B2bPorto
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory;

/**
 * Class ConfigurableGridViewHelper
 *
 * @package Bss\HidePrice\Helper
 */
class ConfigurableGridViewHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CGV_CONFIG_SYSTEM_SECTION_XML_PATH = 'configuablegridview/';
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * ConfigurableGridViewHelper constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SessionFactory $customerSession
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SessionFactory $customerSession,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }

    /**
     * Is module configurable grid view enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCGVEnabled()
    {
        if ($this->isModuleOutputEnabled('Bss_ConfiguableGridView')) {
            $active = $this->getConfig('general/active');
            if ($active == 1) {
                $disabled_customer_group = $this->getConfig('general/disabled_customer_group');
                if ($disabled_customer_group == '') {
                    return true;
                }
                $disabled_customer_group = explode(',', $disabled_customer_group);
                if (!in_array($this->customerSession->create()->getCustomerGroupId(), $disabled_customer_group)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get store config
     *
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig($path, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::CGV_CONFIG_SYSTEM_SECTION_XML_PATH . $path,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }
}
