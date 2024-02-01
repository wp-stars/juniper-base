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

namespace Borlabs\Cookie\System\License;

use Borlabs\Cookie\ApiClient\LicenseApiClient;
use Borlabs\Cookie\Dto\License\LicenseDto;
use Borlabs\Cookie\Exception\ApiClient\LicenseApiClientException;
use Borlabs\Cookie\System\Option\Option;

/**
 * Class License.
 */
class License
{
    private LicenseApiClient $licenseApiClient;

    private ?LicenseDto $licenseData;

    private Option $option;

    public function __construct(
        LicenseApiClient $licenseApiClient,
        Option $option
    ) {
        $this->licenseApiClient = $licenseApiClient;
        $this->option = $option;
    }

    /**
     * Returns the license model ({@see \Borlabs\Cookie\Dto\License\LicenseDto}) if available.
     *
     * @param bool $reload By default, this method checks if license data has already been received. If set to true,
     *                     this method reloads the license data from the database.
     */
    public function get(bool $reload = false): ?LicenseDto
    {
        if ($reload === true || !isset($this->licenseData)) {
            /*
                Such license system, much secure, wow.
                Just kidding, you want all the trouble with updates, just to save some bucks?
                Please support an independent developer and buy a license, thank you :)
            */
            $licenseDataBlog = $this->option->get('LicenseData');
            $licenseData = $licenseDataBlog->value;

            if (!empty($licenseData)) {
                $licenseData = base64_decode($licenseData, true);
                $licenseData = json_decode($licenseData);

                // TODO TEMP
                if (!isset($licenseData->licenseMeta)) {
                    return null;
                }
                // TODO TEMP END

                $this->licenseData = LicenseDto::fromJson($licenseData);

                return $this->licenseData;
            }

            $this->licenseData = null;
        }

        return $this->licenseData instanceof LicenseDto ? $this->licenseData : null;
    }

    /**
     * This method checks whether a license key exists and is valid.
     */
    public function isLicenseValid(): bool
    {
        return isset($this->get()->licenseValidUntil) && $this->get()->licenseValidUntil >= date('Y-m-d');
    }

    /**
     * The method checks if the current build was created before the license date expired.
     */
    public function isLicenseValidForCurrentBuild(): bool
    {
        return !isset($this->get()->licenseValidUntil) || isset($this->get()->licenseValidUntil) && date('ymd', strtotime($this->get()->licenseValidUntil)) >= BORLABS_COOKIE_BUILD;
    }

    /**
     * This method checks if a license exists and is valid for the current build.
     */
    public function isPluginUnlocked(): bool
    {
        // Such license system, much secure, wow.
        // Just kidding, you want all the trouble with updates, just to save some bucks?
        // Please support an independent developer and buy a license, thank you :)
        return isset($this->get()->licenseType) && $this->isLicenseValidForCurrentBuild();
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\LicenseApiClientException
     */
    public function register(string $licenseKey): bool
    {
        $this->save($this->licenseApiClient->register($licenseKey));
        $this->option->set('LicenseLastCheck', date('Ymd'));

        return true;
    }

    public function remove(): bool
    {
        if ($this->get() === null) {
            return true;
        }

        $this->licenseApiClient->unregister($this->get());
        $this->option->delete('LicenseData');
        // get() is now able to set the correct information for licenseData
        $this->get(true);

        return true;
    }

    public function validateLicense(): void
    {
        $lastCheckOption = $this->option->get('LicenseLastCheck', 0);
        $licenseData = $this->get();
        $lastCheck = (int) ($lastCheckOption->value);
        $nextCheck = (int) date(
            'Ymd',
            mktime(
                (int) date('H'),
                (int) date('i'),
                (int) date('s'),
                (int) date('m'),
                ((int) date('d')) - 3,
            ),
        );

        if (isset($licenseData->licenseKey) && $lastCheck < $nextCheck) {
            try {
                $license = $this->licenseApiClient->register($licenseData->licenseKey);

                // Update last check
                $this->option->set('LicenseLastCheck', date('Ymd'));
            } catch (LicenseApiClientException $e) {
                $this->remove();

                return;
            }

            $this->save($license);
        }
    }

    private function save(LicenseDto $licenseData): void
    {
        $this->option->set('LicenseData', base64_encode(json_encode($licenseData)));
        $this->get(true);
    }
}
