<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Communication;

use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\SecurityOauthUser\Communication\Authenticator\OauthUserTokenAuthenticator;
use Spryker\Zed\SecurityOauthUser\Communication\Expander\SecurityBuilderExpander;
use Spryker\Zed\SecurityOauthUser\Communication\Expander\SecurityBuilderExpanderInterface;
use Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin;
use Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\Provider\OauthUserProvider;
use Spryker\Zed\SecurityOauthUser\Communication\Reader\ResourceOwnerReader;
use Spryker\Zed\SecurityOauthUser\Communication\Reader\ResourceOwnerReaderInterface;
use Spryker\Zed\SecurityOauthUser\Communication\Security\Handler\OauthUserAuthenticationFailureHandler;
use Spryker\Zed\SecurityOauthUser\Communication\Security\Handler\OauthUserAuthenticationSuccessHandler;
use Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUser;
use Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUserInterface;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToMessengerFacadeInterface;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserDependencyProvider;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

/**
 * @method \Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig getConfig()
 * @method \Spryker\Zed\SecurityOauthUser\Business\SecurityOauthUserFacadeInterface getFacade()
 */
class SecurityOauthUserCommunicationFactory extends AbstractCommunicationFactory
{
    public function createOauthUserProvider(): UserProviderInterface
    {
        return new OauthUserProvider();
    }

    public function createSecurityOauthUser(UserTransfer $userTransfer): SecurityOauthUserInterface
    {
        return new SecurityOauthUser($userTransfer, $this->getConfig()->getOauthUserRoles());
    }

    public function createPostAuthenticationGuardToken(UserInterface $user, string $providerKey): GuardTokenInterface
    {
        return new PostAuthenticationGuardToken(
            $user,
            $providerKey,
            $user->getRoles(),
        );
    }

    public function createResourceOwnerReader(): ResourceOwnerReaderInterface
    {
        return new ResourceOwnerReader($this->getFacade());
    }

    public function createOauthUserAuthenticationSuccessHandler(): AuthenticationSuccessHandlerInterface
    {
        return new OauthUserAuthenticationSuccessHandler($this->getUserFacade(), $this->getConfig());
    }

    public function createOauthUserAuthenticationFailureHandler(): AuthenticationFailureHandlerInterface
    {
        return new OauthUserAuthenticationFailureHandler($this->getMessengerFacade(), $this->getConfig());
    }

    public function getUserFacade(): SecurityOauthUserToUserFacadeInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::FACADE_USER);
    }

    public function getMessengerFacade(): SecurityOauthUserToMessengerFacadeInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::FACADE_MESSENGER);
    }

    public function getRouter(): ChainRouterInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::SERVICE_ROUTER);
    }

    public function createOauthUserTokenAuthenticator(): AuthenticatorInterface
    {
        return new OauthUserTokenAuthenticator(
            $this->createResourceOwnerReader(),
            $this->createOauthUserAuthenticationSuccessHandler(),
            $this->createOauthUserAuthenticationFailureHandler(),
            $this->getConfig(),
            $this->createOauthUserProvider(),
        );
    }

    public function createSecurityBuilderExpander(): SecurityBuilderExpanderInterface
    {
        if (class_exists(AuthenticationProviderManager::class) === true) {
            return new OauthUserSecurityPlugin();
        }

        return new SecurityBuilderExpander(
            $this->createOauthUserProvider(),
            $this->getConfig(),
            $this->createOauthUserTokenAuthenticator(),
        );
    }
}
