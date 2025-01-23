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
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;

class CheckoutRedirect implements ObserverInterface
{
    protected $responseFactory;
    protected $url;
    protected $scopeConfig;
    protected $request;

    /**
     * Constructor method
     *
     * @param ResponseFactory $responseFactory,
     * @param UrlInterface $url,
     * @param ScopeConfigInterface $scopeConfig,
     * @param RequestInterface $request
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isEnabled = $this->scopeConfig->getValue('viraxpress_config/checkout_theme_switcher/enable_vira_checkout', ScopeInterface::SCOPE_STORE);

        // Get the current action name
        $action = $this->request->getFullActionName();

        // Check if the action is 'checkout_index_index' and if the feature is enabled
        if ($isEnabled && $action == 'checkout_index_index') {
            $customCheckoutUrl = $this->url->getUrl('onepage/checkout');
            $this->responseFactory->create()->setRedirect($customCheckoutUrl)->sendResponse();
            exit;
        }
    }
}