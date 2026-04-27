<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityOauthUser\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserDependencyProvider;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserAuthenticationStrategyPluginInterface;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserPostResolvePluginInterface;
use SprykerTest\Zed\SecurityOauthUser\SecurityOauthUserBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityOauthUser
 * @group Business
 * @group Facade
 * @group ResolveOauthUserByResourceOwnerTest
 * Add your own group annotations below this line
 */
class ResolveOauthUserByResourceOwnerTest extends Unit
{
    protected const string TEST_PROVIDER = 'test-provider';

    protected const string TEST_EXTERNAL_ID = 'external-user-123';

    protected SecurityOauthUserBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY, []);
        $this->tester->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_POST_RESOLVE, []);
    }

    public function testResolveOauthUserByResourceOwnerReturnsUserWhenStrategyPluginResolves(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser();

        $strategyPlugin = $this->createStrategyPluginMock($userTransfer);
        $this->tester->setDependency(
            SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY,
            [$strategyPlugin],
        );

        $resourceOwnerTransfer = $this->buildResourceOwner($userTransfer->getUsername());

        // Act
        $resolvedUserTransfer = $this->tester->getSecurityOauthUserFacade()->resolveOauthUserByResourceOwner($resourceOwnerTransfer);

        // Assert
        $this->assertNotNull($resolvedUserTransfer, 'Expected user to be resolved by strategy plugin.');
        $this->assertSame($userTransfer->getUsername(), $resolvedUserTransfer->getUsername());
    }

    public function testResolveOauthUserByResourceOwnerFallsBackToEmailStrategyWhenNoPluginApplies(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser();

        $securityOauthUserFacade = $this->tester->mockSecurityOauthUserFacade(
            SecurityOauthUserConfig::AUTHENTICATION_STRATEGY_ACCEPT_ONLY_EXISTING_USERS,
        );

        $this->tester->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY, []);

        $resourceOwnerTransfer = $this->buildResourceOwner($userTransfer->getUsername());

        // Act
        $resolvedUserTransfer = $securityOauthUserFacade->resolveOauthUserByResourceOwner($resourceOwnerTransfer);

        // Assert
        $this->assertNotNull($resolvedUserTransfer, 'Expected user to be resolved via email fallback.');
        $this->assertSame($userTransfer->getUsername(), $resolvedUserTransfer->getUsername());
    }

    public function testResolveOauthUserByResourceOwnerReturnsNullWhenNoUserResolved(): void
    {
        // Arrange
        $securityOauthUserFacade = $this->tester->mockSecurityOauthUserFacade(
            SecurityOauthUserConfig::AUTHENTICATION_STRATEGY_ACCEPT_ONLY_EXISTING_USERS,
        );

        $this->tester->setDependency(SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY, []);

        $resourceOwnerTransfer = $this->buildResourceOwner('nonexistent@spryker.com');

        // Act
        $resolvedUserTransfer = $securityOauthUserFacade->resolveOauthUserByResourceOwner($resourceOwnerTransfer);

        // Assert
        $this->assertNull($resolvedUserTransfer, 'Expected null when no user can be resolved.');
    }

    public function testResolveOauthUserByResourceOwnerReturnsNullWhenNoPluginsWiredAndNoEmail(): void
    {
        // Arrange
        $resourceOwnerTransfer = (new ResourceOwnerTransfer())
            ->setProvider(static::TEST_PROVIDER)
            ->setId(static::TEST_EXTERNAL_ID);

        // Act
        $resolvedUserTransfer = $this->tester->getSecurityOauthUserFacade()->resolveOauthUserByResourceOwner($resourceOwnerTransfer);

        // Assert
        $this->assertNull($resolvedUserTransfer, 'Expected null when no plugins wired and no email for fallback.');
    }

    public function testResolveOauthUserByResourceOwnerExecutesPostResolvePluginsAfterSuccessfulResolution(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser();

        $strategyPlugin = $this->createStrategyPluginMock($userTransfer);
        $this->tester->setDependency(
            SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_AUTHENTICATION_STRATEGY,
            [$strategyPlugin],
        );

        $postResolvePlugin = $this->getMockBuilder(OauthUserPostResolvePluginInterface::class)->getMock();
        $postResolvePlugin->expects($this->once())->method('postResolve');

        $this->tester->setDependency(
            SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_POST_RESOLVE,
            [$postResolvePlugin],
        );

        $resourceOwnerTransfer = $this->buildResourceOwner($userTransfer->getUsername());

        // Act
        $this->tester->getSecurityOauthUserFacade()->resolveOauthUserByResourceOwner($resourceOwnerTransfer);
    }

    public function testResolveOauthUserByResourceOwnerDoesNotExecutePostResolveWhenResolutionFails(): void
    {
        // Arrange
        $resourceOwnerTransfer = (new ResourceOwnerTransfer())
            ->setProvider(static::TEST_PROVIDER)
            ->setId(static::TEST_EXTERNAL_ID);

        $postResolvePlugin = $this->getMockBuilder(OauthUserPostResolvePluginInterface::class)->getMock();
        $postResolvePlugin->expects($this->never())->method('postResolve');

        $this->tester->setDependency(
            SecurityOauthUserDependencyProvider::PLUGINS_OAUTH_USER_POST_RESOLVE,
            [$postResolvePlugin],
        );

        // Act
        $this->tester->getSecurityOauthUserFacade()->resolveOauthUserByResourceOwner($resourceOwnerTransfer);
    }

    protected function buildResourceOwner(string $email): ResourceOwnerTransfer
    {
        return (new ResourceOwnerTransfer())
            ->setEmail($email)
            ->setProvider(static::TEST_PROVIDER)
            ->setId(static::TEST_EXTERNAL_ID);
    }

    protected function createStrategyPluginMock(UserTransfer $userTransfer): OauthUserAuthenticationStrategyPluginInterface
    {
        $mock = $this->getMockBuilder(OauthUserAuthenticationStrategyPluginInterface::class)->getMock();
        $mock->method('isApplicable')->willReturn(true);
        $mock->method('resolveOauthUser')->willReturn($userTransfer);

        return $mock;
    }
}
