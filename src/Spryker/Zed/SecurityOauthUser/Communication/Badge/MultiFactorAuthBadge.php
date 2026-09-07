<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Communication\Badge;

use Generated\Shared\Transfer\MultiFactorAuthTransfer;
use Generated\Shared\Transfer\MultiFactorAuthValidationRequestTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationCodeInvalidatorPluginInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class MultiFactorAuthBadge implements BadgeInterface
{
    /**
     * @uses \Spryker\Zed\MultiFactorAuth\Communication\Plugin\AuthenticationHandler\User\UserMultiFactorAuthenticationHandlerPlugin::USER_MULTI_FACTOR_AUTHENTICATION_HANDLER_NAME
     */
    protected const string USER_MULTI_FACTOR_AUTHENTICATION_HANDLER_NAME = 'USER_MULTI_FACTOR_AUTHENTICATION';

    protected bool $isRequired = false;

    protected bool $isResolved = true;

    protected ?int $status = null;

    /**
     * @param array<\Spryker\Zed\SecurityGuiExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface> $userMultiFactorAuthenticationHandlerPlugins
     */
    public function __construct(protected array $userMultiFactorAuthenticationHandlerPlugins)
    {
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function setIsResolved(bool $isResolved): void
    {
        $this->isResolved = $isResolved;
    }

    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    public function getIsRequired(): bool
    {
        return $this->isRequired;
    }

    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function enable(UserTransfer $userTransfer)
    {
        foreach ($this->userMultiFactorAuthenticationHandlerPlugins as $plugin) {
            if ($plugin->isApplicable(static::USER_MULTI_FACTOR_AUTHENTICATION_HANDLER_NAME) === false) {
                continue;
            }

            $multiFactorAuthValidationRequestTransfer = (new MultiFactorAuthValidationRequestTransfer())
                ->setUser($userTransfer)
                ->setIsLogin(true);
            $multiFactorAuthValidationResponseTransfer = $plugin->validateUserMultiFactorStatus($multiFactorAuthValidationRequestTransfer);

            if ($multiFactorAuthValidationResponseTransfer->getIsRequired() === true && $plugin instanceof AuthenticationCodeInvalidatorPluginInterface) {
                $multiFactorAuthTransfer = (new MultiFactorAuthTransfer())->setUser($userTransfer);
                $plugin->invalidateUserCodes($multiFactorAuthTransfer);
            }

            $this->setIsRequired($multiFactorAuthValidationResponseTransfer->getIsRequiredOrFail());
            $this->setStatus($multiFactorAuthValidationResponseTransfer->getStatus());
        }

        return $this;
    }
}
