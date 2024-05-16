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

namespace Borlabs\Cookie\Model\StyleBlocker;

use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\Model\AbstractModel;

final class StyleBlockerModel extends AbstractModel
{
    public ?string $borlabsServicePackageKey;

    public ?KeyValueDtoList $handles;

    public string $key;

    public string $name;

    public ?KeyValueDtoList $phrases;

    public bool $status = false;

    public bool $undeletable = false;
}
