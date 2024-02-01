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

namespace Borlabs\Cookie\Dto\Config;

use Borlabs\Cookie\Enum\System\AutomaticUpdateEnum;

/**
 * The **PluginDto** class is used as a typed object that is passed within the system.
 *
 * The object specifies the criteria for updating the plugin.
 *
 * @see \Borlabs\Cookie\System\Config\PluginConfig
 */
final class PluginDto extends AbstractConfigDto
{
    public AutomaticUpdateEnum $automaticUpdate;

    public bool $enableDebugLogging = false;
}
