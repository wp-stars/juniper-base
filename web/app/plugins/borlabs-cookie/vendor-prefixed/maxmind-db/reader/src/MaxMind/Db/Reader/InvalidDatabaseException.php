<?php
/**
 * @license Apache-2.0
 *
 * Modified by borlabs on 31-January-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Borlabs\Cookie\Dependencies\MaxMind\Db\Reader;

use Exception;

/**
 * This class should be thrown when unexpected data is found in the database.
 */
class InvalidDatabaseException extends Exception
{
}
