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

namespace Borlabs\Cookie\System\Script;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Repository\Service\ServiceRepository;

class FallbackCodeManager
{
    private ServiceRepository $serviceRepository;

    private WpFunction $wpFunction;

    public function __construct(ServiceRepository $serviceRepository, WpFunction $wpFunction)
    {
        $this->serviceRepository = $serviceRepository;
        $this->wpFunction = $wpFunction;
    }

    public function getFallbackCodes(): string
    {
        $services = $this->serviceRepository->getAllOfCurrentLanguage(false, true);
        $return = '';

        foreach ($services as $service) {
            if ($service->fallbackCode !== '') {
                $settings = array_column($service->settingsFields->list, 'value', 'key');

                if (isset($settings['disable-code-execution']) && $settings['disable-code-execution'] === '1') {
                    continue;
                }

                $searchAndReplace = [
                    'search' => array_map(
                        static fn ($value) => '{{ ' . $value . ' }}',
                        array_column($service->settingsFields->list ?? [], 'key'),
                    ),
                    'replace' => array_column($service->settingsFields->list ?? [], 'value'),
                ];

                $searchAndReplace = $this->wpFunction->applyFilter(
                    'borlabsCookie/scriptBuilder/service/modifyPlaceholders/' . $service->key,
                    $searchAndReplace,
                );
                $return .= str_replace($searchAndReplace['search'], $searchAndReplace['replace'], $service->fallbackCode);
            }
        }

        return $return;
    }
}
