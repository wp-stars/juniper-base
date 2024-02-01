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

namespace Borlabs\Cookie\System\ResourceEnqueuer;

use Borlabs\Cookie\Adapter\WpFunction;

final class ResourceEnqueuer
{
    public const HANDLE_PREFIX = 'borlabs-cookie';

    private WpFunction $wpFunction;

    public function __construct(
        WpFunction $wpFunction
    ) {
        $this->wpFunction = $wpFunction;
    }

    /**
     * @param string $name the prefix `borlabs-cookie-` is added automatically
     * @param string $path the path to the file relative to the plugin directory or an URL
     */
    public function enqueueScript(
        string $name,
        string $path,
        ?array $dependency = null,
        ?string $version = null,
        ?bool $placeInFooter = null
    ): void {
        $this->wpFunction->wpEnqueueScript(
            self::HANDLE_PREFIX . '-' . $name,
            filter_var($path, FILTER_VALIDATE_URL) ? $path : $this->wpFunction->pluginsUrl($path, BORLABS_COOKIE_BASENAME),
            $dependency,
            BORLABS_COOKIE_VERSION . ($version ? '-' . $version : ''),
            $placeInFooter,
        );
    }

    /**
     * @param string $name the prefix `borlabs-cookie-` is added automatically
     * @param string $path the path to the file relative to the plugin directory or an URL
     */
    public function enqueueStyle(string $name, string $path, ?array $dependency = null, ?string $version = null): void
    {
        $this->wpFunction->wpEnqueueStyle(
            self::HANDLE_PREFIX . '-' . $name,
            filter_var($path, FILTER_VALIDATE_URL) ? $path : $this->wpFunction->pluginsUrl($path, BORLABS_COOKIE_BASENAME),
            $dependency,
            BORLABS_COOKIE_VERSION . ($version ? '-' . $version : ''),
        );
    }
}
