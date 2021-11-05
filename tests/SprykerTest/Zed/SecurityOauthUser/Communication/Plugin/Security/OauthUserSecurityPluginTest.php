<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityOauthUser\Communication\Plugin\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Shared\Security\Configuration\SecurityConfiguration;
use Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityOauthUser
 * @group Communication
 * @group Plugin
 * @group Security
 * @group OauthUserSecurityPluginTest
 * Add your own group annotations below this line
 */
class OauthUserSecurityPluginTest extends Unit
{
    /**
     * @uses \Spryker\Zed\Session\Communication\Plugin\Application\SessionApplicationPlugin::SERVICE_SESSION
     *
     * @var string
     */
    protected const SERVICE_SESSION = 'session';

    /**
     * @uses \Spryker\Zed\Security\Communication\Plugin\Application\SecurityApplicationPlugin::SERVICE_SECURITY_TOKEN_STORAGE
     *
     * @var string
     */
    protected const SERVICE_SECURITY_TOKEN_STORAGE = 'security.token_storage';

    /**
     * @var string
     */
    protected const SOME_EMAIL = 'some@email.com';

    /**
     * @var string
     */
    protected const SOME_CODE = 'SOME_CODE';

    /**
     * @var string
     */
    protected const SOME_STATE = 'SOME_STATE';

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin::SECURITY_FIREWALL_NAME
     *
     * @var string
     */
    protected const SECURITY_FIREWALL_NAME = 'OauthUser';

    /**
     * @uses \Spryker\Zed\SecurityGui\Communication\Plugin\Security\UserSecurityPlugin::SECURITY_FIREWALL_NAME
     *
     * @var string
     */
    protected const SECURITY_USER_FIREWALL_NAME = 'User';

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin::SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR
     *
     * @var string
     */
    protected const SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR = 'security.oauth_user.token.authenticator';

    /**
     * @var \SprykerTest\Zed\SecurityOauthUser\SecurityOauthUserCommunicationTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->tester->enableSecurityApplicationPlugin();
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->addRoute('test', '/ignorable', function () {
            return new Response('test-text');
        });
    }

    /**
     * @return void
     */
    public function testOauthUserCanLogin(): void
    {
        // Arrange
        $container = $this->tester->getContainer();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => static::SOME_EMAIL,
        ]);

        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(true, $userTransfer->getUsername()),
        );

        $securityPlugin = new OauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());
        $this->tester->addSecurityPlugin($securityPlugin);

        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNull($token);

        $container->get(static::SERVICE_SESSION)->start();
        $httpKernelBrowser = $this->tester->getHttpKernelBrowser();

        // Act
        $httpKernelBrowser->request('get', '/ignorable');
        $httpKernelBrowser->request(
            'get',
            '/security-oauth-user/login',
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        /** @var \Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUser $user */
        $user = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken()->getUser();
        $this->assertSame($userTransfer->getUsername(), $user->getUsername(), 'Expected that usernames match.');
    }

    /**
     * @return void
     */
    public function testOauthUserFirewallExpandUserFirewall(): void
    {
        // Arrange
        $securityPlugin = new OauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());

        $securityBuilder = (new SecurityConfiguration())
            ->addFirewall('User', []);

        // Act
        $securityBuilder = $securityPlugin->extend($securityBuilder, $this->tester->getContainer());

        // Assert
        $firewalls = $securityBuilder->getConfiguration()->getFirewalls();

        $this->assertNull($firewalls[static::SECURITY_FIREWALL_NAME] ?? null);
        $this->assertNotNull($firewalls[static::SECURITY_USER_FIREWALL_NAME]['users']);
        $this->assertSame(
            static::SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR,
            $firewalls[static::SECURITY_USER_FIREWALL_NAME]['guard']['authenticators'][0],
        );
    }

    /**
     * @return void
     */
    public function testOauthUserFirewallAddOauthUserFirwallToSecurityService(): void
    {
        // Arrange
        $securityPlugin = new OauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());

        // Act
        $securityBuilder = $securityPlugin->extend(new SecurityConfiguration(), $this->tester->getContainer());

        // Assert
        $firewalls = $securityBuilder->getConfiguration()->getFirewalls();

        $this->assertNull($firewalls[static::SECURITY_USER_FIREWALL_NAME] ?? null);
        $this->assertNotNull($firewalls[static::SECURITY_FIREWALL_NAME]['users']);
        $this->assertSame(
            static::SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR,
            $firewalls[static::SECURITY_FIREWALL_NAME]['guard']['authenticators'][0],
        );
    }

    /**
     * @return void
     */
    public function testOauthUserWithInvalidCredentialsCanNotLogin(): void
    {
        // Arrange
        $container = $this->tester->getContainer();

        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(false),
        );

        $securityPlugin = new OauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());
        $this->tester->addSecurityPlugin($securityPlugin);

        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNull($token);

        $container->get(static::SERVICE_SESSION)->start();
        $httpKernelBrowser = $this->tester->getHttpKernelBrowser();

        // Act
        $httpKernelBrowser->request(
            'get',
            '/security-oauth-user/login',
            ['code' => static::SOME_CODE, 'state' => static::SOME_EMAIL],
        );

        // Assert
        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNull($token, 'Expected that user with invalid credentials can not login.');
    }

    /**
     * @return void
     */
    public function testIgnorablePathsAreAccessible(): void
    {
        // Arrange
        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNull($token);

        $httpKernelBrowser = $this->tester->getHttpKernelBrowser();

        $securityPlugin = new OauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());
        $this->tester->addSecurityPlugin($securityPlugin);

        // Act
        $httpKernelBrowser->request('get', '/ignorable');

        // Assert
        $this->assertSame(
            'test-text',
            $httpKernelBrowser->getResponse()->getContent(),
            'Expected that ignorable paths are accessible.',
        );
    }

    /**
     * @param bool $successFlow
     * @param string|null $email
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface
     */
    protected function createOauthUserClientStrategyPluginMock(
        bool $successFlow,
        ?string $email = null
    ): OauthUserClientStrategyPluginInterface {
        $oauthUserClientStrategyPluginMock = $this->getMockBuilder(OauthUserClientStrategyPluginInterface::class)
            ->getMock();

        $oauthUserClientStrategyPluginMock
            ->method('isApplicable')
            ->willReturn($successFlow);

        $resourceOwnerResponseTransfer = (new ResourceOwnerResponseTransfer())
            ->setIsSuccessful($successFlow);

        if ($successFlow) {
            $resourceOwnerResponseTransfer->setResourceOwner(
                (new ResourceOwnerTransfer())->setEmail($email),
            );
        }

        $oauthUserClientStrategyPluginMock
            ->method('getResourceOwner')
            ->willReturn($resourceOwnerResponseTransfer);

        return $oauthUserClientStrategyPluginMock;
    }
}
