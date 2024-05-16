<?php
/**
 * @license Apache-2.0
 *
 * Modified by borlabs on 31-January-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Borlabs\Cookie\Dependencies\MaxMind\Db\Reader;

class Util
{
    /**
     * @param resource $stream
     */
    public static function read($stream, int $offset, int $numberOfBytes): string
    {
        if ($numberOfBytes === 0) {
            return '';
        }
        if (fseek($stream, $offset) === 0) {
            $value = fread($stream, $numberOfBytes);

            // We check that the number of bytes read is equal to the number
            // asked for. We use ftell as getting the length of $value is
            // much slower.
            if ($value !== false && ftell($stream) - $offset === $numberOfBytes) {
                return $value;
            }
        }

        throw new InvalidDatabaseException(
            'The MaxMind DB file contains bad data'
        );
    }
}
