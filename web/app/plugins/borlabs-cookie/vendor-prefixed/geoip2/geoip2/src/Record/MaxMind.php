<?php
/**
 * @license Apache-2.0
 *
 * Modified by borlabs on 31-January-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Borlabs\Cookie\Dependencies\GeoIp2\Record;

/**
 * Contains data about your account.
 *
 * This record is returned by all location services and databases.
 *
 * @property-read int|null $queriesRemaining The number of remaining queries you
 * have for the service you are calling.
 */
class MaxMind extends AbstractRecord
{
    /**
     * @ignore
     *
     * @var array<string>
     */
    protected $validAttributes = ['queriesRemaining'];
}
