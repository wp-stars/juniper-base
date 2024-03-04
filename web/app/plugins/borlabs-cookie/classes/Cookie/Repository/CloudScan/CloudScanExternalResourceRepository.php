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

namespace Borlabs\Cookie\Repository\CloudScan;

use Borlabs\Cookie\Dto\Repository\BelongsToRelationDto;
use Borlabs\Cookie\Dto\Repository\PropertyMapDto;
use Borlabs\Cookie\Dto\Repository\PropertyMapItemDto;
use Borlabs\Cookie\Dto\Repository\PropertyMapRelationItemDto;
use Borlabs\Cookie\Model\CloudScan\CloudScanExternalResourceModel;
use Borlabs\Cookie\Model\CloudScan\CloudScanModel;
use Borlabs\Cookie\Repository\AbstractRepository;
use Borlabs\Cookie\Repository\RepositoryInterface;

/**
 * @extends AbstractRepository<CloudScanExternalResourceModel>
 */
final class CloudScanExternalResourceRepository extends AbstractRepository implements RepositoryInterface
{
    public const MODEL = CloudScanExternalResourceModel::class;

    public const TABLE = 'borlabs_cookie_cloud_scan_external_resources';

    protected const UNDELETABLE = false;

    public static function propertyMap(): PropertyMapDto
    {
        return new PropertyMapDto([
            new PropertyMapItemDto('id', 'id'),
            new PropertyMapItemDto('cloudScanId', 'cloud_scan_id'),
            new PropertyMapItemDto('borlabsServicePackageKey', 'borlabs_service_package_key'),
            new PropertyMapItemDto('examples', 'examples'),
            new PropertyMapItemDto('hostname', 'hostname'),
            new PropertyMapRelationItemDto(
                'cloudScan',
                new BelongsToRelationDto(
                    CloudScanRepository::class,
                    'cloudScanId',
                    'id',
                    'externalResources',
                ),
            ),
        ]);
    }

    /**
     * @return array<CloudScanExternalResourceModel>
     */
    public function getByCloudScan(CloudScanModel $cloudScanModel): array
    {
        return $this->getByCloudScanId($cloudScanModel->id);
    }

    /**
     * @return array<CloudScanExternalResourceModel>
     */
    public function getByCloudScanId(int $id): array
    {
        return $this->find([
            'cloudScanId' => $id,
        ]);
    }
}