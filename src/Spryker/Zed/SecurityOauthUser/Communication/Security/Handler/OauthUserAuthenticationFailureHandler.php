<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Communication\Security\Handler;

use Generated\Shared\Transfer\MessageTransfer;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToMessengerFacadeInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OauthUserAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var \Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToMessengerFacadeInterface
     */
    protected $messengerFacade;

    /**
     * @var \Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig
     */
    protected $securityOauthUserConfig;

    public function __construct(
        SecurityOauthUserToMessengerFacadeInterface $messengerFacade,
        SecurityOauthUserConfig $securityOauthUserConfig
    ) {
        $this->messengerFacade = $messengerFacade;
        $this->securityOauthUserConfig = $securityOauthUserConfig;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->messengerFacade->addErrorMessage(
            (new MessageTransfer())->setValue('Authentication failed!'),
        );

        return new RedirectResponse($this->securityOauthUserConfig->getUrlLogin());
    }
}
