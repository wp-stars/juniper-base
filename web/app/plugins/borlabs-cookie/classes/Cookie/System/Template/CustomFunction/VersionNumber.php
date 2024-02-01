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

use Borlabs\Cookie\ApiClient\Transformer\PackageTransformer;
use Borlabs\Cookie\Dependencies\Twig\TwigFunction;
use Borlabs\Cookie\Dto\Package\VersionNumberDto;
use Borlabs\Cookie\System\Template\Template;

final class VersionNumber
{
    private PackageTransformer $packageTransformer;

    private Template $template;

    public function __construct(Template $template, PackageTransformer $packageTransformer)
    {
        $this->template = $template;
        $this->packageTransformer = $packageTransformer;
    }

    public function register()
    {
        $this->template->getTwig()->addFunction(
            new TwigFunction('versionNumber', function (VersionNumberDto $versionNumber) {
                return $this->packageTransformer->versionNumberToString($versionNumber);
            }),
        );
    }
}
