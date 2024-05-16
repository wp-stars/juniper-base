<?php
/*
 *  Copyright (c) 2024 Borlabs GmbH. All rights reserved.
 *  This file may not be redistributed in whole or significant part.
 *  Content of this file is protected by international copyright laws.
 *
 *  ----------------- Borlabs Cookie IS NOT FREE SOFTWARE -----------------
 *
 *  @copyright Borlabs GmbH, https://borlabs.io
 */

declare(strict_types=1);

namespace Borlabs\Cookie\Repository\Expression;

use Borlabs\Cookie\Repository\RepositoryQueryPart;

class DirectionDescExpression extends AbstractExpression
{
    public function toWpSqlQueryPart(): RepositoryQueryPart
    {
        return new RepositoryQueryPart('DESC', []);
    }
}
