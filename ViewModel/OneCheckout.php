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

namespace ViraXpress\CheckoutOptimization\ViewModel;

use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

class OneCheckout implements ArgumentInterface
{

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param CustomerSession $customerSession
     * @param TokenFactory $tokenModelFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyInterface $localeCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     */
    public function __construct(
        CustomerSession $customerSession,
        TokenFactory $tokenModelFactory,
        PriceCurrencyInterface $priceCurrency,
        CurrencyInterface $localeCurrency,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->localeCurrency = $localeCurrency;
        $this->customerSession = $customerSession;
        $this->attributeMapper = $attributeMapper;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * Get address attributes.
     *
     * @return array
     */
    public function getAddressAttributes()
    {
        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }
        return $elements;
    }

    /**
     * Get Customer token.
     *
     * @return string
     */
    public function getCustomerToken()
    {
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $customerToken = $this->tokenModelFactory->create();
            $tokenKey = $customerToken->createCustomerToken($customerId)->getToken();
            return $tokenKey;
        } else {
            return null;
        }
    }

    /**
     * Get current currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStoreCode()
    {
        $currentStoreCode = $this->storeManager->getStore()->getCode();
        return $currentStoreCode;
    }

    /**
     * Get the locale code of the current store.
     *
     * @return string The locale code.
     */
    public function getStoreLocale()
    {
        $storeId =  $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
