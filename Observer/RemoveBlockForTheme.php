<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_CheckoutOptimization
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

namespace ViraXpress\CheckoutOptimization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RemoveBlockForTheme implements ObserverInterface
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Http $request,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Remove block based on theme store view
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $isEnabled = $this->scopeConfig->getValue('viraxpress_config/checkout_theme_switcher/enable_vira_checkout', ScopeInterface::SCOPE_STORE, $this->getStoreId());
        $action = $this->request->getFullActionName();
        if ($isEnabled && $action == 'checkout_index_index') {
            $blockList = ['requirejs-config', 'cookie-status-check', 'page.main.title', 'copyright', 'authentication-popup', 'pagebuilder.widget.initializer', 'frontend-storage-manager', 'customer.section.config', 'customer.data.invalidation.rules', 'customer.customer.data', 'pageCache', 'form_key_provider'];
            foreach ($blockList as $list) {
                $block = $layout->getBlock($list);
                if ($block) $layout->unsetElement($list);
            }
        }
        if ($isEnabled && $action != 'checkout_index_index') {
            $blockList = ['checkout_head'];
            foreach ($blockList as $list) {
                $block = $layout->getBlock($list);
                if ($block) $layout->unsetElement($list);
            }
        }
    }

    /* 
     * Get current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
