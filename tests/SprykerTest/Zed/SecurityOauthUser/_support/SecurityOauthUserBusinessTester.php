<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityOauthUser;

use Codeception\Actor;
use Spryker\Zed\SecurityOauthUser\Business\SecurityOauthUserBusinessFactory;
use Spryker\Zed\SecurityOauthUser\Business\SecurityOauthUserFacadeInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserDependencyProvider;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserRestrictionPluginInterface;
use Spryker\Zed\User\Business\UserFacadeInterface;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(\SprykerTest\Zed\SecurityOauthUser\PHPMD)
 */
class SecurityOauthUserBusinessTester extends Actor
{
    use _generated\SecurityOauthUserBusinessTesterActions;

    public function getSecurityOauthUserFacade(): SecurityOauthUserFacadeInterface
    {
        return $this->getLocator()->securityOauthUser()->facade();
    }

    public function getUserFacade(): UserFacadeInterface
    {
        return $this->getLocator()->user()->facade();
    }

    public function setOauthUserClientStrategyPlugin(
        OauthUserClientStrategyPluginInterface $oauthUserClientStrategyPlugin
    ): void {
        $this->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_CLIENT_STRATEGY, [
            $oauthUserClientStrategyPlugin,
        ]);
    }

    public function setOauthUserRestrictionPlugin(
        OauthUserRestrictionPluginInterface $oauthUserRestrictionPlugin
    ): void {
        $this->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_RESTRICTION, [
            $oauthUserRestrictionPlugin,
        ]);
    }

    public function mockSecurityOauthUserFacade(
        string $authenticationStrategy,
        ?string $groupName = null
    ): SecurityOauthUserFacadeInterface {
        $this->mockConfigMethod('getAuthenticationStrategy', function () use ($authenticationStrategy) {
            return $authenticationStrategy;
        });

        $mockConfig = $this->mockConfigMethod('getOauthUserGroupName', function () use ($groupName) {
            return $groupName;
        });

        $securityOauthUserBusinessFactory = (new SecurityOauthUserBusinessFactory())
            ->setConfig($mockConfig);

        return $this->getSecurityOauthUserFacade()->setFactory($securityOauthUserBusinessFactory);
    }
}
