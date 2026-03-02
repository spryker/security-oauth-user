<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\SecurityOauthUser\Business\Adder\AclGroupAdder;
use Spryker\Zed\SecurityOauthUser\Business\Adder\AclGroupAdderInterface;
use Spryker\Zed\SecurityOauthUser\Business\Checker\OauthUserRestrictionChecker;
use Spryker\Zed\SecurityOauthUser\Business\Checker\OauthUserRestrictionCheckerInterface;
use Spryker\Zed\SecurityOauthUser\Business\Creator\OauthUserCreator;
use Spryker\Zed\SecurityOauthUser\Business\Creator\OauthUserCreatorInterface;
use Spryker\Zed\SecurityOauthUser\Business\Executor\AuthenticationStrategyExecutor;
use Spryker\Zed\SecurityOauthUser\Business\Executor\AuthenticationStrategyExecutorInterface;
use Spryker\Zed\SecurityOauthUser\Business\Reader\ResourceOwnerReader;
use Spryker\Zed\SecurityOauthUser\Business\Reader\ResourceOwnerReaderInterface;
use Spryker\Zed\SecurityOauthUser\Business\Strategy\AuthenticationStrategyInterface;
use Spryker\Zed\SecurityOauthUser\Business\Strategy\CreateUserAuthenticationStrategy;
use Spryker\Zed\SecurityOauthUser\Business\Strategy\ExistingUserAuthenticationStrategy;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToAclFacadeInterface;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeInterface;
use Spryker\Zed\SecurityOauthUser\Dependency\Service\SecurityOauthUserToUtilTextServiceInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserDependencyProvider;

/**
 * @method \Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig getConfig()
 */
class SecurityOauthUserBusinessFactory extends AbstractBusinessFactory
{
    public function createResourceOwnerReader(): ResourceOwnerReaderInterface
    {
        return new ResourceOwnerReader($this->getOauthUserClientStrategyPlugins());
    }

    public function createOauthUserRestrictionChecker(): OauthUserRestrictionCheckerInterface
    {
        return new OauthUserRestrictionChecker($this->getOauthUserRestrictionPlugins());
    }

    public function createAclGroupAdder(): AclGroupAdderInterface
    {
        return new AclGroupAdder($this->getAclFacade());
    }

    public function createOauthUserCreator(): OauthUserCreatorInterface
    {
        return new OauthUserCreator(
            $this->getConfig(),
            $this->getUserFacade(),
            $this->getUtilTextService(),
            $this->createAclGroupAdder(),
        );
    }

    public function createAuthenticationStrategyExecutor(): AuthenticationStrategyExecutorInterface
    {
        return new AuthenticationStrategyExecutor($this->getConfig(), $this->getAuthenticationStrategyList());
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUser\Business\Strategy\AuthenticationStrategyInterface>
     */
    public function getAuthenticationStrategyList(): array
    {
        return [
            $this->createExistingUserAuthenticationStrategy(),
            $this->createCreateUserAuthenticationStrategy(),
        ];
    }

    public function createExistingUserAuthenticationStrategy(): AuthenticationStrategyInterface
    {
        return new ExistingUserAuthenticationStrategy($this->getConfig(), $this->getUserFacade());
    }

    public function createCreateUserAuthenticationStrategy(): AuthenticationStrategyInterface
    {
        return new CreateUserAuthenticationStrategy(
            $this->getConfig(),
            $this->getUserFacade(),
            $this->createOauthUserCreator(),
        );
    }

    public function getUtilTextService(): SecurityOauthUserToUtilTextServiceInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::SERVICE_UTIL_TEXT);
    }

    public function getUserFacade(): SecurityOauthUserToUserFacadeInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::FACADE_USER);
    }

    public function getAclFacade(): SecurityOauthUserToAclFacadeInterface
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::FACADE_ACL);
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface>
     */
    public function getOauthUserClientStrategyPlugins(): array
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_CLIENT_STRATEGY);
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserRestrictionPluginInterface>
     */
    public function getOauthUserRestrictionPlugins(): array
    {
        return $this->getProvidedDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_RESTRICTION);
    }
}
