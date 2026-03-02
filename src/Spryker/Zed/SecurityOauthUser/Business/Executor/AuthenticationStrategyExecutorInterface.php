<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Business\Executor;

use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;

interface AuthenticationStrategyExecutorInterface
{
    public function resolveOauthUser(UserCriteriaTransfer $userCriteriaTransfer): ?UserTransfer;
}
