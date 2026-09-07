<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToAclFacadeBridge;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToMessengerFacadeBridge;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeBridge;
use Spryker\Zed\SecurityOauthUser\Dependency\Service\SecurityOauthUserToUtilTextServiceBridge;

/**
 * @method \Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig getConfig()
 */
class SecurityOauthUserDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string FACADE_USER = 'FACADE_USER';

    public const string FACADE_ACL = 'FACADE_ACL';

    public const string FACADE_MESSENGER = 'FACADE_MESSENGER';

    public const string SERVICE_UTIL_TEXT = 'SERVICE_UTIL_TEXT';

    public const string PLUGINS_OAUTH_USER_CLIENT_STRATEGY = 'PLUGINS_OAUTH_USER_CLIENT_STRATEGY';

    public const string PLUGINS_OAUTH_USER_RESTRICTION = 'PLUGINS_OAUTH_USER_RESTRICTION';

    public const string PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY = 'PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY';

    public const string PLUGINS_USER_AUTHENTICATION_HANDLER = 'PLUGINS_USER_AUTHENTICATION_HANDLER';

    public const string PLUGINS_OAUTH_USER_POST_RESOLVE = 'PLUGINS_OAUTH_USER_POST_RESOLVE';

    /**
     * @see \Spryker\Shared\Application\Application::SERVICE_ROUTER
     */
    public const string SERVICE_ROUTER = 'routers';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addUserFacade($container);
        $container = $this->addMessengerFacade($container);
        $container = $this->addRouter($container);
        $container = $this->addUserAuthenticationHandlerPlugins($container);

        return $container;
    }

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addUserFacade($container);
        $container = $this->addAclFacade($container);
        $container = $this->addUtilTextService($container);
        $container = $this->addOauthUserClientStrategyPlugins($container);
        $container = $this->addOauthUserRestrictionPlugins($container);
        $container = $this->addOauthUserAuthenticationStrategyPlugins($container);
        $container = $this->addOauthUserPostResolvePlugins($container);

        return $container;
    }

    protected function addUserFacade(Container $container): Container
    {
        $container->set(static::FACADE_USER, function (Container $container) {
            return new SecurityOauthUserToUserFacadeBridge(
                $container->getLocator()->user()->facade(),
            );
        });

        return $container;
    }

    protected function addAclFacade(Container $container): Container
    {
        $container->set(static::FACADE_ACL, function (Container $container) {
            return new SecurityOauthUserToAclFacadeBridge(
                $container->getLocator()->acl()->facade(),
            );
        });

        return $container;
    }

    protected function addMessengerFacade(Container $container): Container
    {
        $container->set(static::FACADE_MESSENGER, function (Container $container) {
            return new SecurityOauthUserToMessengerFacadeBridge(
                $container->getLocator()->messenger()->facade(),
            );
        });

        return $container;
    }

    protected function addUtilTextService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_TEXT, function (Container $container) {
            return new SecurityOauthUserToUtilTextServiceBridge(
                $container->getLocator()->utilText()->service(),
            );
        });

        return $container;
    }

    protected function addOauthUserClientStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_OAUTH_USER_CLIENT_STRATEGY, function () {
            return $this->getOauthUserClientStrategyPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface>
     */
    protected function getOauthUserClientStrategyPlugins(): array
    {
        return [];
    }

    protected function addOauthUserRestrictionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_OAUTH_USER_RESTRICTION, function () {
            return $this->getOauthUserRestrictionPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserRestrictionPluginInterface>
     */
    protected function getOauthUserRestrictionPlugins(): array
    {
        return [];
    }

    protected function addOauthUserAuthenticationStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY, function () {
            return $this->getOauthUserAuthenticationStrategyPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserAuthenticationStrategyPluginInterface>
     */
    protected function getOauthUserAuthenticationStrategyPlugins(): array
    {
        return [];
    }

    protected function addOauthUserPostResolvePlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_OAUTH_USER_POST_RESOLVE, function () {
            return $this->getOauthUserPostResolvePlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserPostResolvePluginInterface>
     */
    protected function getOauthUserPostResolvePlugins(): array
    {
        return [];
    }

    protected function addRouter(Container $container): Container
    {
        $container->set(static::SERVICE_ROUTER, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_ROUTER);
        });

        return $container;
    }

    protected function addUserAuthenticationHandlerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_USER_AUTHENTICATION_HANDLER, function () {
            return $this->getUserAuthenticationHandlerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface>
     */
    protected function getUserAuthenticationHandlerPlugins(): array
    {
        return [];
    }
}
