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

namespace Borlabs\Cookie\System\Template\CustomFunction;

use Borlabs\Cookie\Dependencies\Twig\TwigFunction;
use Borlabs\Cookie\System\FileSystem\CacheFolder;
use Borlabs\Cookie\System\Template\Template;

final class CacheUrl
{
    private CacheFolder $cacheFolder;

    private Template $template;

    public function __construct(CacheFolder $cacheFolder, Template $template)
    {
        $this->cacheFolder = $cacheFolder;
        $this->template = $template;
    }

    public function register()
    {
        $this->template->getTwig()->addFunction(
            new TwigFunction('cacheUrl', function (string $file) {
                return $this->cacheFolder->getUrl() . '/' . $file;
            }),
        );
    }
}
