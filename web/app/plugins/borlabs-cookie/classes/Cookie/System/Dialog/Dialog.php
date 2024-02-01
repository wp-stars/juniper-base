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

namespace Borlabs\Cookie\System\Dialog;

use Borlabs\Cookie\System\Config\DialogSettingsConfig;
use Borlabs\Cookie\System\GeoIp\GeoIp;

final class Dialog
{
    private DialogSettingsConfig $dialogSettingsConfig;

    private GeoIp $geoIp;

    public function __construct(
        DialogSettingsConfig $dialogSettingsConfig,
        GeoIp $geoIp
    ) {
        $this->dialogSettingsConfig = $dialogSettingsConfig;
        $this->geoIp = $geoIp;
    }

    public function output(): void
    {
        if ($this->dialogSettingsConfig->get()->geoIpActive && !$this->dialogSettingsConfig->get()->geoIpCachingMode) {
            $disableDialog = !$this->geoIp->getShowDialogStatusForCurrentUser();
        } else {
            $disableDialog = false;
        }

        // Disable indexing of Borlabs Cookie data
        echo '<!--googleoff: all-->';
        echo "<div data-nosnippet data-borlabs-cookie-disable-dialog='" . ($disableDialog ? 'true' : 'false')
            . "' id='BorlabsCookieBox'></div><div id='BorlabsCookieWidget' class='brlbs-cmpnt-container'></div>";
        echo '<!--googleon: all-->';
    }
}
