<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Framework\App\RequestInterface;

class Authentication
{
    /** @var StorageInterface */
    protected $credential;

    /** @var RequestInterface */
    protected $requestInterface;

    /**
     *
     * @param StorageInterface $credential
     * @param RequestInterface $requestInterface
     * @return void
     */
    public function __construct(
        StorageInterface $credential,
        RequestInterface $requestInterface
    ) {
        $this->credential = $credential;
        $this->requestInterface = $requestInterface;
    }
    /**
     * Validate admin user via authorization header
     *
     * @return void
     * @throws GraphQlInputException
     */
    public function authorize()
    {
        $authHeader = $this->requestInterface->getServer('HTTP_AUTHORIZATION');
        if ($authHeader=='') {
            throw new GraphQlInputException(__('Authorization header missing or empty'));
        } else {
            $auth = explode("|", $authHeader);
            if (count($auth)!=2) {
                throw new GraphQlInputException(__('Authorization header missing username|password'));
            } else {
                if (!$this->credential->authenticate($auth[0], $auth[1])) {
                    throw new GraphQlInputException(__('Admin login failed'));
                }
            }
        }
    }
}
