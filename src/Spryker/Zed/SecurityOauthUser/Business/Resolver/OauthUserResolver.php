<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\SecurityOauthUser\Business\Resolver;

use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\SecurityOauthUser\Business\Executor\AuthenticationStrategyExecutorInterface;

class OauthUserResolver implements OauthUserResolverInterface
{
    /**
     * @param array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserAuthenticationStrategyPluginInterface> $oauthUserAuthenticationStrategyPlugins
     * @param array<\Spryker\Zed\SecurityOauthUserExtension\Dependency\Plugin\OauthUserPostResolvePluginInterface> $oauthUserPostResolvePlugins
     */
    public function __construct(
        protected array $oauthUserAuthenticationStrategyPlugins,
        protected array $oauthUserPostResolvePlugins,
        protected AuthenticationStrategyExecutorInterface $authenticationStrategyExecutor,
    ) {
    }

    public function resolveOauthUserByResourceOwner(ResourceOwnerTransfer $resourceOwnerTransfer): ?UserTransfer
    {
        $userTransfer = $this->resolveViaPlugins($resourceOwnerTransfer);

        if ($userTransfer === null) {
            $userTransfer = $this->resolveViaEmailFallback($resourceOwnerTransfer);
        }

        if ($userTransfer !== null) {
            $this->runPostResolvePlugins($userTransfer, $resourceOwnerTransfer);
        }

        return $userTransfer;
    }

    protected function resolveViaPlugins(ResourceOwnerTransfer $resourceOwnerTransfer): ?UserTransfer
    {
        foreach ($this->oauthUserAuthenticationStrategyPlugins as $oauthUserAuthenticationStrategyPlugin) {
            if (!$oauthUserAuthenticationStrategyPlugin->isApplicable($resourceOwnerTransfer)) {
                continue;
            }

            $userTransfer = $oauthUserAuthenticationStrategyPlugin->resolveOauthUser($resourceOwnerTransfer);

            if ($userTransfer !== null) {
                return $userTransfer;
            }
        }

        return null;
    }

    protected function resolveViaEmailFallback(ResourceOwnerTransfer $resourceOwnerTransfer): ?UserTransfer
    {
        $email = $resourceOwnerTransfer->getEmail();

        if ($email === null) {
            return null;
        }

        return $this->authenticationStrategyExecutor->resolveOauthUser(
            (new UserCriteriaTransfer())->setEmail($email),
        );
    }

    protected function runPostResolvePlugins(UserTransfer $userTransfer, ResourceOwnerTransfer $resourceOwnerTransfer): void
    {
        foreach ($this->oauthUserPostResolvePlugins as $oauthUserPostResolvePlugin) {
            $oauthUserPostResolvePlugin->postResolve($userTransfer, $resourceOwnerTransfer);
        }
    }
}
