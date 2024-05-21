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

namespace Borlabs\Cookie\Model\CloudScan;

use Borlabs\Cookie\DtoList\CloudScan\PagesDtoList;
use Borlabs\Cookie\Enum\CloudScan\CloudScanStatusEnum;
use Borlabs\Cookie\Enum\CloudScan\CloudScanTypeEnum;
use Borlabs\Cookie\Model\AbstractModel;
use DateTimeInterface;

final class CloudScanModel extends AbstractModel
{
    public DateTimeInterface $createdAt;

    public string $externalId;

    /**
     * @var array<array-key, CloudScanExternalResourceModel>
     */
    public ?array $externalResources;

    public ?PagesDtoList $pages;

    public CloudScanStatusEnum $status;

    /**
     * @var array<array-key, CloudScanSuggestionModel>
     */
    public ?array $suggestions;

    public CloudScanTypeEnum $type;
}
