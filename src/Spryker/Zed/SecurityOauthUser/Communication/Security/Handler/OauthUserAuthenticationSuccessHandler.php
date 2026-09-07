<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Communication\Security\Handler;

use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OauthUserAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin::SECURITY_FIREWALL_NAME
     *
     * @var string
     */
    protected const SECURITY_FIREWALL_NAME = 'OauthUser';

    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \Spryker\Zed\SecurityGui\Communication\Plugin\Security\Handler\UserAuthenticationSuccessHandler::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY
     */
    protected const string MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY = '_multi_factor_auth_login_user_email';

    /**
     * @uses \Spryker\Zed\MultiFactorAuth\Communication\Controller\UserOauthMultiFactorAuthFlowController::ROUTE_USER_OAUTH_MFA
     */
    protected const string ROUTE_USER_OAUTH_MFA = '/multi-factor-auth/user-oauth-multi-factor-auth-flow/get-user-oauth-login-enabled-types';

    /**
     * @var \Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeInterface
     */
    protected $userFacade;

    /**
     * @var \Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig
     */
    protected $securityOauthUserConfig;

    public function __construct(
        SecurityOauthUserToUserFacadeInterface $userFacade,
        SecurityOauthUserConfig $securityOauthUserConfig
    ) {
        $this->userFacade = $userFacade;
        $this->securityOauthUserConfig = $securityOauthUserConfig;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var \Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUserInterface $user */
        $user = $token->getUser();

        if (in_array(static::ACCESS_MODE_PRE_AUTH, $token->getRoleNames(), true)) {
            $request->getSession()->set(
                static::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY,
                $user->getUserTransfer()->getUsername(),
            );

            return new RedirectResponse(static::ROUTE_USER_OAUTH_MFA);
        }

        $this->userFacade->setCurrentUser($user->getUserTransfer());

        return $this->createRedirectResponse($request);
    }

    protected function createRedirectResponse(Request $request): RedirectResponse
    {
        $targetUrl = $this->getTargetPath($request->getSession(), static::SECURITY_FIREWALL_NAME);

        if ($targetUrl) {
            return new RedirectResponse($targetUrl);
        }

        return new RedirectResponse($this->securityOauthUserConfig->getUrlHome());
    }
}
