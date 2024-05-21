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

namespace Borlabs\Cookie\Repository\ConsentStatistic;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\Dto\Repository\PropertyMapDto;
use Borlabs\Cookie\Dto\Repository\PropertyMapItemDto;
use Borlabs\Cookie\Model\ConsentStatistic\ConsentStatisticByHourEntryModel;
use Borlabs\Cookie\Repository\AbstractRepository;
use Borlabs\Cookie\Repository\ConsentStatistic\Traits\StatisticRepositoryMethodsTrait;
use Borlabs\Cookie\Repository\RepositoryInterface;
use Borlabs\Cookie\System\Config\GeneralConfig;

/**
 * @extends AbstractRepository<ConsentStatisticByHourEntryModel>
 *
 * @mixin StatisticRepositoryMethodsTrait<ConsentStatisticByHourEntryModel>
 */
final class ConsentStatisticByHourRepository extends AbstractRepository implements RepositoryInterface
{
    use StatisticRepositoryMethodsTrait;

    public const MODEL = ConsentStatisticByHourEntryModel::class;

    public const TABLE = 'borlabs_cookie_consent_statistic_by_hour';

    public static function propertyMap(): PropertyMapDto
    {
        return new PropertyMapDto([
            new PropertyMapItemDto('id', 'id'),
            new PropertyMapItemDto('cookieVersion', 'cookie_version'),
            new PropertyMapItemDto('count', 'count'),
            new PropertyMapItemDto('date', 'date'),
            new PropertyMapItemDto('hour', 'hour'),
            new PropertyMapItemDto('isAnonymous', 'is_anonymous'),
            new PropertyMapItemDto('serviceGroupKey', 'service_group_key'),
            new PropertyMapItemDto('serviceKey', 'service_key'),
        ]);
    }

    protected Container $container;

    protected WpDb $wpdb;

    private GeneralConfig $generalConfig;

    public function __construct(
        Container $container,
        GeneralConfig $generalConfig,
        WpDb $wpdb
    ) {
        $this->container = $container;
        $this->generalConfig = $generalConfig;
        $this->wpdb = $wpdb;

        parent::__construct($this->container, $this->wpdb);
    }

    protected function getDefaultTablePrefix(): string
    {
        return $this->generalConfig->get()->aggregateConsents ? $this->wpdb->base_prefix : $this->wpdb->prefix;
    }
}
