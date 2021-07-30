<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Business\Adder;

use Generated\Shared\Transfer\UserTransfer;

interface AclGroupAdderInterface
{
    /**
     * @param \Generated\Shared\Transfer\UserTransfer $userTransfer
     * @param string $groupName
     *
     * @throws \Spryker\Zed\SecurityOauthUser\Business\Exception\AclGroupNotFoundException
     *
     * @return void
     */
    public function addOauthUserToGroup(UserTransfer $userTransfer, string $groupName): void;
}
