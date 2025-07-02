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

use Magento\Framework\Escaper;
use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;

class OneCheckout implements ArgumentInterface
{

    /**
     * Get country path
     */
    public const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var CheckoutAgreementsListInterface
     */
    private $checkoutAgreementsList;

    /**
     * @var ActiveStoreAgreementsFilter
     */
    private $activeStoreAgreementsFilter;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

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
     * @param Escaper $escaper
     * @param CustomerSession $customerSession
     * @param TokenFactory $tokenModelFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyInterface $localeCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param ActiveStoreAgreementsFilter $activeStoreAgreementsFilter
     */
    public function __construct(
        Escaper $escaper,
        CustomerSession $customerSession,
        TokenFactory $tokenModelFactory,
        PriceCurrencyInterface $priceCurrency,
        CurrencyInterface $localeCurrency,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        CheckoutAgreementsListInterface $checkoutAgreementsList,
        ActiveStoreAgreementsFilter $activeStoreAgreementsFilter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->localeCurrency = $localeCurrency;
        $this->customerSession = $customerSession;
        $this->attributeMapper = $attributeMapper;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->checkoutAgreementsList = $checkoutAgreementsList ?: ObjectManager::getInstance()->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->activeStoreAgreementsFilter = $activeStoreAgreementsFilter ?: ObjectManager::getInstance()->get(
            ActiveStoreAgreementsFilter::class
        );
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->escaper = $escaper;
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

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
        ) ?? '';
    }

    /**
     * Returns agreements config.
     *
     * @return array
     */
    public function getAgreementsConfig()
    {
        $agreementConfiguration = [];
        $isAgreementsEnabled = $this->scopeConfig->isSetFlag(
            \Magento\CheckoutAgreements\Model\AgreementsProvider::PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $agreementsList = $this->checkoutAgreementsList->getList(
            $this->activeStoreAgreementsFilter->buildSearchCriteria()
        );
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            $agreementConfiguration['agreements'][] = [
                'content' => $agreement->getIsHtml()
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'checkboxText' => $this->escaper->escapeHtml($agreement->getCheckboxText()),
                'mode' => $agreement->getMode(),
                'agreementId' => $agreement->getAgreementId(),
                'contentHeight' => $agreement->getContentHeight()
            ];
        }

        return $agreementConfiguration;
    }
}
