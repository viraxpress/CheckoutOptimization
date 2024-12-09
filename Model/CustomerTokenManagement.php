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

namespace ViraXpress\CheckoutOptimization\Model;

use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use ViraXpress\CheckoutOptimization\Api\CustomerTokenManagementInterface;

class CustomerTokenManagement implements CustomerTokenManagementInterface
{

    /**
     * @var TokenFactory
     */
    protected $tokenFactory;
    
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param TokenFactory $tokenFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        TokenFactory $tokenFactory,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Return customer token by customer id
     *
     * @param int $customerId
     */
    public function getCustomerToken($customerId)
    {
        $customer = $this->customerRepositoryInterface->getById($customerId);
        if (!$customer) {
            throw new NoSuchEntityException(__('Customer with ID "%1" does not exist.', $customerId));
        }
        $token = $this->tokenFactory->create()->createCustomerToken($customerId);
        return $token->getToken();
    }
}
