<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Business\Strategy;

use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;

interface AuthenticationStrategyInterface
{
    public function getAuthenticationStrategy(): string;

    public function resolveOauthUser(UserCriteriaTransfer $userCriteriaTransfer): ?UserTransfer;
}
