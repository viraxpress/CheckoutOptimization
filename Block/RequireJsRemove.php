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

namespace ViraXpress\CheckoutOptimization\Block;

use Magento\RequireJs\Block\Html\Head\Config;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Minification;

class RequireJsRemove extends Config
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
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     * @param \Magento\Framework\View\Element\Context $context
     * @param RequireJsConfig $config
     * @param \Magento\RequireJs\Model\FileManager $fileManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Asset\ConfigInterface $bundleConfig
     * @param Minification $minification
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $request,
        \Magento\Framework\View\Element\Context $context,
        RequireJsConfig $config,
        \Magento\RequireJs\Model\FileManager $fileManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Asset\ConfigInterface $bundleConfig,
        Minification $minification,
        array $data = []
    ) {
        parent::__construct($context, $config, $fileManager, $pageConfig, $bundleConfig, $minification, $data);
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Include RequireJs configuration as an asset on the page, overriding _prepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // Fetch the config value
        $isEnabled = $this->scopeConfig->getValue('viraxpress_config/checkout_theme_switcher/enable_vira_checkout', ScopeInterface::SCOPE_STORE);
        $action = $this->request->getFullActionName();

        // Perform the custom logic here based on $isEnabled and $action
        if ($isEnabled && $action == 'checkout_index_index') {
            return $this;
        }

        return parent::_prepareLayout();
    }
}
