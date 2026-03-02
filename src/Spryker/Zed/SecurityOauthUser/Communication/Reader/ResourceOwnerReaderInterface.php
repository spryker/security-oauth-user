<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityOauthUser\Communication\Reader;

use Generated\Shared\Transfer\ResourceOwnerTransfer;
use Symfony\Component\HttpFoundation\Request;

interface ResourceOwnerReaderInterface
{
    public function getResourceOwner(Request $request): ?ResourceOwnerTransfer;
}
