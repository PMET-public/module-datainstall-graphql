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
    /**
     * @var User
     */
    private User $userResource;
    /**
     * @var UserInterfaceFactory
     */
    private UserInterfaceFactory $userFactory;
    /**
     * @var IntegrationServiceInterface
     */
    private IntegrationServiceInterface $integration;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var Provider
     */
    private Provider $provider;

    /**
     * @param User $userResource
     * @param UserInterfaceFactory $userFactory
     * @param IntegrationServiceInterface $integration
     * @param CustomerRepositoryInterface $customerRepository
     * @param Provider $provider
     */
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
     * Get Creator details by ID
     *
     * @param int $type
     * @param mixed $id
     * @param int $quoteId
     * @return string
     */
    public function retrieveCreatorById(int $type, $id, int $quoteId = null): string
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
