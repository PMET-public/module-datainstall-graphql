<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Sales;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\NegotiableQuote\Model\Purged\Provider;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\User\Model\ResourceModel\User;

class Creator
{
    private User $userResource;
    private UserInterfaceFactory $userFactory;
    private IntegrationServiceInterface $integration;
    private CustomerRepositoryInterface $customerRepository;
    private Provider $provider;

    public function __construct(
        User $userResource,
        UserInterfaceFactory $userFactory,
        IntegrationServiceInterface $integration,
        CustomerRepositoryInterface $customerRepository,
        Provider $provider
    ) {
        $this->userResource = $userResource;
        $this->userFactory = $userFactory;
        $this->integration = $integration;
        $this->customerRepository = $customerRepository;
        $this->provider = $provider;
    }

    /**
     * @param $type
     * @param $id
     * @param $quoteId
     * @return string
     */
    public function retrieveCreatorById($type, $id, $quoteId = null): string
    {
        if ($type == UserContextInterface::USER_TYPE_ADMIN) {
            try {
                $user = $this->userFactory->create();
                $this->userResource->load($user, $id);
                return $user->getUserName();
            } catch (NoSuchEntityException $e) {
                if ($quoteId) {
                    return $this->provider->getSalesRepresentativeName($quoteId);
                }
            }
        } elseif ($type == UserContextInterface::USER_TYPE_INTEGRATION) {
            try {
                $integration = $this->integration->get($id);
                return $integration->getName();
            } catch (IntegrationException $e) {
                return 'System';
            }
        } elseif ($type == UserContextInterface::USER_TYPE_CUSTOMER) {
            try {
                $customer = $this->customerRepository->getById($id);
                return $customer->getEmail();
            } catch (NoSuchEntityException|LocalizedException $e) {
                if ($quoteId) {
                    return $this->provider->getCompanyEmail($quoteId);
                }
            }
        }

        return 'System';
    }
}
