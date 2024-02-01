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

namespace Borlabs\Cookie\Enum\System;

use Borlabs\Cookie\Enum\AbstractEnum;
use Borlabs\Cookie\Enum\LocalizedEnumInterface;

/**
 * @method static AutomaticUpdateEnum AUTO_UPDATE_ALL()
 * @method static AutomaticUpdateEnum AUTO_UPDATE_MINOR()
 * @method static AutomaticUpdateEnum AUTO_UPDATE_NONE()
 */
class AutomaticUpdateEnum extends AbstractEnum implements LocalizedEnumInterface
{
    public const AUTO_UPDATE_ALL = 'auto-update-all';

    public const AUTO_UPDATE_MINOR = 'auto-update-minor';

    public const AUTO_UPDATE_NONE = 'auto-update-none';

    public static function localized(): array
    {
        return [
            self::AUTO_UPDATE_ALL => _x('<translation-key id="All-versions">All versions</translation-key>', 'Backend / Plugin Update / Option', 'borlabs-cookie'),
            self::AUTO_UPDATE_MINOR => _x('<translation-key id="Minor-versions-only">Minor versions only</translation-key>', 'Backend / Plugin Update / Option', 'borlabs-cookie'),
            self::AUTO_UPDATE_NONE => _x('<translation-key id="No-automatic-update">No automatic update</translation-key>', 'Backend / Plugin Update / Option', 'borlabs-cookie'),
        ];
    }
}
