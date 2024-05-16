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

namespace Borlabs\Cookie\System\Installer;

use Borlabs\Cookie\Container\Container;

class DefaultEntriesManager
{
    public array $registry = [];

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getDefaultEntries(): array
    {
        $defaultEntries = [];

        foreach ($this->registry as $defaultEntryModel) {
            $defaultEntries[] = $this->container->get($defaultEntryModel)->getDefaultModel();
        }

        return $defaultEntries;
    }
}
