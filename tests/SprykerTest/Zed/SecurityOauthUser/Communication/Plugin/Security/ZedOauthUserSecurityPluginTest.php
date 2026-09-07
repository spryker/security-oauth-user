<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityOauthUser\Communication\Plugin\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MultiFactorAuthValidationResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserTransfer;
use ReflectionClass;
use Spryker\Shared\Security\Configuration\SecurityConfiguration;
use Spryker\Zed\Security\Communication\Configurator\SecurityConfigurator;
use Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface;
use Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\ZedOauthUserSecurityPlugin;
use Spryker\Zed\SecurityOauthUser\Communication\Security\Handler\OauthUserAuthenticationSuccessHandler;
use Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUserInterface;
use Spryker\Zed\SecurityOauthUser\Dependency\Facade\SecurityOauthUserToUserFacadeInterface;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserConfig;
use Spryker\Zed\SecurityOauthUser\SecurityOauthUserDependencyProvider;
use Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserClientStrategyPluginInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityOauthUser
 * @group Communication
 * @group Plugin
 * @group Security
 * @group ZedOauthUserSecurityPluginTest
 * Add your own group annotations below this line
 */
class ZedOauthUserSecurityPluginTest extends Unit
{
    /**
     * @uses \Spryker\Zed\Session\Communication\Plugin\Application\SessionApplicationPlugin::SERVICE_SESSION
     *
     * @var string
     */
    protected const SERVICE_SESSION = 'session';

    /**
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
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Security\Handler\OauthUserAuthenticationSuccessHandler::ROUTE_USER_OAUTH_MFA
     */
    protected const string ROUTE_USER_OAUTH_MFA = '/multi-factor-auth/user-oauth-multi-factor-auth-flow/get-user-oauth-login-enabled-types';

    /**
     * @uses \Spryker\Zed\SecurityGui\Communication\Plugin\Security\Handler\UserAuthenticationSuccessHandler::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY
     */
    protected const string MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY = '_multi_factor_auth_login_user_email';

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Authenticator\OauthUserTokenAuthenticator::ACCESS_MODE_PRE_AUTH
     */
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Expander\SecurityBuilderExpander::OAUTH_MFA_ROUTE_PATTERN
     */
    protected const string OAUTH_MFA_ROUTE_PATTERN = '^/multi-factor-auth/user-oauth-multi-factor-auth-flow';

    /**
     * @uses \Spryker\Shared\MultiFactorAuth\MultiFactorAuthConstants::CODE_BLOCKED
     */
    protected const int CODE_BLOCKED = 1;

    /**
     * @uses \Spryker\Zed\SecurityOauthUser\Communication\Plugin\Security\OauthUserSecurityPlugin::SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR
     *
     * @var string
     */
    protected const SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR = 'security.OauthUser.token.authenticator';

    /**
     * @var \SprykerTest\Zed\SecurityOauthUser\SecurityOauthUserCommunicationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->tester->isSymfonyVersion5() === true) {
            $this->markTestSkipped('Compatible only with `symfony/security-core` package version >= 6. Will be enabled by default once Symfony 5 support is discontinued.');
        }

        $this->tester->addRoute('test', '/ignorable', function () {
            return new Response('test-text');
        });

        $this->tester->addRoute(SecurityOauthUserConfig::ROUTE_NAME_OAUTH_USER_LOGIN, '/security-oauth-user/login', function () {
            // Only exists to make the router for tests finding the requested route.
        });

        $this->tester->mockSecurityDependencies();
    }

    /**
     * The security plugin captures the module's dependency container the moment its factory is built,
     * so every dependency a test relies on must be registered before this method runs.
     *
     * @param array<\Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface> $multiFactorAuthHandlerPlugins
     */
    protected function bootOauthSecurity(array $multiFactorAuthHandlerPlugins = []): void
    {
        $this->tester->setDependency(
            SecurityOauthUserDependencyProvider::PLUGINS_USER_AUTHENTICATION_HANDLER,
            $multiFactorAuthHandlerPlugins,
        );

        $securityPlugin = new ZedOauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());
        $this->tester->addSecurityPlugin($securityPlugin);
        $this->tester->enableSecurityApplicationPlugin();
    }

    public function testOauthUserCanLogin(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => static::SOME_EMAIL,
        ]);

        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(true, $userTransfer->getUsername()),
        );
        $this->bootOauthSecurity();

        $container = $this->tester->getContainer();

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
        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();

        /** @var \Spryker\Zed\SecurityOauthUser\Communication\Security\SecurityOauthUser $user */
        $user = $token->getUser();
        $this->assertSame($userTransfer->getUsername(), $user->getUsername(), 'Expected that usernames match.');
        $this->assertNotContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a full (non pre-auth) token when Multi-Factor Authentication is not required.',
        );
    }

    public function testOauthUserGetsPreAuthTokenWhenMultiFactorAuthIsRequired(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => static::SOME_EMAIL,
        ]);

        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(true, $userTransfer->getUsername()),
        );
        $this->bootOauthSecurity([$this->createMfaAuthenticationHandlerPluginMock(true)]);

        $container = $this->tester->getContainer();
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
        $this->assertNotNull($token, 'Expected the OAuth user to be authenticated.');
        $this->assertContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a pre-auth token (ACCESS_MODE_PRE_AUTH) when Multi-Factor Authentication is required.',
        );
    }

    public function testOauthUserGetsPreAuthTokenWhenMultiFactorAuthCodeIsBlocked(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => static::SOME_EMAIL,
        ]);

        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(true, $userTransfer->getUsername()),
        );
        $this->bootOauthSecurity([$this->createMfaAuthenticationHandlerPluginMock(false, static::CODE_BLOCKED)]);

        $container = $this->tester->getContainer();
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
        $this->assertNotNull($token, 'Expected the OAuth user to be authenticated.');
        $this->assertContains(
            static::ACCESS_MODE_PRE_AUTH,
            $token->getRoleNames(),
            'Expected a pre-auth token (ACCESS_MODE_PRE_AUTH) when the Multi-Factor Authentication code is blocked.',
        );
    }

    public function testOauthUserFirewallExpandUserFirewall(): void
    {
        // Arrange
        $securityPlugin = new ZedOauthUserSecurityPlugin();
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
            $firewalls[static::SECURITY_USER_FIREWALL_NAME]['form']['authenticators'][0],
        );
    }

    public function testOauthUserFirewallAddOauthUserFirwallToSecurityService(): void
    {
        // Arrange
        $securityPlugin = new ZedOauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());

        // Act
        $securityBuilder = $securityPlugin->extend(new SecurityConfiguration(), $this->tester->getContainer());

        // Assert
        $firewalls = $securityBuilder->getConfiguration()->getFirewalls();

        $this->assertNull($firewalls[static::SECURITY_USER_FIREWALL_NAME] ?? null);
        $this->assertNotNull($firewalls[static::SECURITY_FIREWALL_NAME]['users']);
        $this->assertSame(
            static::SECURITY_OAUTH_USER_TOKEN_AUTHENTICATOR,
            $firewalls[static::SECURITY_FIREWALL_NAME]['form']['authenticators'][0],
        );
    }

    public function testOauthUserWithInvalidCredentialsCanNotLogin(): void
    {
        // Arrange
        $this->tester->setOauthUserClientStrategyPlugin(
            $this->createOauthUserClientStrategyPluginMock(false),
        );
        $this->bootOauthSecurity();

        $container = $this->tester->getContainer();

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

    public function testIgnorablePathsAreAccessible(): void
    {
        // Arrange
        $this->bootOauthSecurity();

        $container = $this->tester->getContainer();
        $container->get(static::SERVICE_SESSION)->start();

        $token = $container->get(static::SERVICE_SECURITY_TOKEN_STORAGE)->getToken();
        $this->assertNull($token);

        $httpKernelBrowser = $this->tester->getHttpKernelBrowser();

        // Act
        $httpKernelBrowser->request('get', '/ignorable');

        // Assert
        $this->assertSame(
            'test-text',
            $httpKernelBrowser->getResponse()->getContent(),
            'Expected that ignorable paths are accessible.',
        );
    }

    public function testOauthUserSuccessHandlerRedirectsToMfaPageOnPreAuth(): void
    {
        // Arrange
        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);

        $userTransfer = (new UserTransfer())->setUsername(static::SOME_EMAIL);

        $userMock = $this->getMockBuilder(SecurityOauthUserInterface::class)->getMock();
        $userMock->method('getUserTransfer')->willReturn($userTransfer);

        $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMock();
        $tokenMock->method('getRoleNames')->willReturn([static::ACCESS_MODE_PRE_AUTH]);
        $tokenMock->method('getUser')->willReturn($userMock);

        $userFacadeMock = $this->getMockBuilder(SecurityOauthUserToUserFacadeInterface::class)->getMock();

        $handler = new OauthUserAuthenticationSuccessHandler($userFacadeMock, new SecurityOauthUserConfig());

        // Act
        $response = $handler->onAuthenticationSuccess($request, $tokenMock);

        // Assert
        $this->assertSame(
            static::ROUTE_USER_OAUTH_MFA,
            $response->headers->get('Location'),
            'Expected redirect to MFA page when token has ACCESS_MODE_PRE_AUTH role.',
        );

        $this->assertSame(
            static::SOME_EMAIL,
            $session->get(static::MULTI_FACTOR_AUTH_LOGIN_USER_EMAIL_SESSION_KEY),
            'Expected user email stored in session for MFA flow.',
        );
    }

    public function testMfaAccessRuleIsConfiguredForPreAuth(): void
    {
        // Arrange
        $securityPlugin = new ZedOauthUserSecurityPlugin();
        $securityPlugin->setFactory($this->tester->getCommunicationFactory());

        $securityBuilder = new SecurityConfiguration();

        // Act
        $securityBuilder = $securityPlugin->extend($securityBuilder, $this->tester->getContainer());

        // Assert
        $accessRules = $securityBuilder->getConfiguration()->getAccessRules();

        $mfaRule = array_values(array_filter($accessRules, static function (array $rule): bool {
            return $rule[0] === static::OAUTH_MFA_ROUTE_PATTERN && $rule[1] === static::ACCESS_MODE_PRE_AUTH;
        }));

        $this->assertNotEmpty(
            $mfaRule,
            sprintf(
                'Expected access rule [%s, %s] to be registered in the security builder.',
                static::OAUTH_MFA_ROUTE_PATTERN,
                static::ACCESS_MODE_PRE_AUTH,
            ),
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface
     */
    protected function createMfaAuthenticationHandlerPluginMock(
        bool $isRequired,
        ?int $status = null
    ): AuthenticationHandlerPluginInterface {
        $pluginMock = $this->getMockBuilder(AuthenticationHandlerPluginInterface::class)->getMock();

        $pluginMock->method('isApplicable')->willReturn(true);

        $pluginMock->method('validateUserMultiFactorStatus')->willReturn(
            (new MultiFactorAuthValidationResponseTransfer())
                ->setIsRequired($isRequired)
                ->setStatus($status),
        );

        return $pluginMock;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setValue(null);
    }
}
