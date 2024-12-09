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

declare(strict_types=1);

namespace ViraXpress\CheckoutOptimization\Controller\Address;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;

class State extends Action
{

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DirectoryHelper $directoryHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DirectoryHelper $directoryHelper,
        JsonFactory $resultJsonFactory
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute save action
     *
     * @return void
     */
    public function execute()
    {
        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();
        $countryId = $this->getRequest()->getParam('event_country');
        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            if (array_key_exists($countryId, $regionsData)) {
                foreach ($regionsData[$countryId] as $key => $region) {
                    $output[$key] = $region['name'];
                }
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($output);
    }
}
