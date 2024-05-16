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

namespace Borlabs\Cookie\Support;

use Borlabs\Cookie\Adapter\WpDb;

/**
 * Static class Database.
 *
 * This class contains a collection of various static methods that can be used to check conditions and perform tasks
 * that are database related.
 *
 * @see \Borlabs\Cookie\Support\Database::columnExists() This method checks whether the column exists in the table.
 * @see \Borlabs\Cookie\Support\Database::dropIndex() This method removes the index from the specified table.
 * @see \Borlabs\Cookie\Support\Database::indexExists() This method checks whether the index exists in the table.
 * @see \Borlabs\Cookie\Support\Database::isDataType() This method checks whether the column data type matches the
 *     expected type.
 * @see \Borlabs\Cookie\Support\Database::isTableEmpty() This method checks whether a table is empty.
 * @see \Borlabs\Cookie\Support\Database::renameTable() This method renames a table.
 * @see \Borlabs\Cookie\Support\Database::tableExists() This method checks whether a table exists.
 */
final class Database
{
    /**
     * This method checks whether the column exists in the table.
     */
    public static function columnExists(string $columnName, string $tableName): bool
    {
        $dbName = WpDb::getInstance()->dbname;

        // HyperDB workaround
        if (empty($dbName) && defined('DB_NAME')) {
            $dbName = DB_NAME;
        }

        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SELECT
                    `COLUMN_NAME`
                FROM
                    `information_schema`.`COLUMNS`
                WHERE
                    `TABLE_SCHEMA` = %s
                    AND
                    `TABLE_NAME` = %s
                    AND
                    `COLUMN_NAME` = %s
            ',
                [
                    $dbName,
                    $tableName,
                    $columnName,
                ],
            ),
        );

        return !(empty($tableResult[0]->COLUMN_NAME));
    }

    /**
     * This method removes the index from the specified table.
     */
    public static function dropIndex(string $indexName, string $tableName): bool
    {
        $queryResult = WpDb::getInstance()->query(
            '
            ALTER TABLE
                `' . $tableName . '`
            DROP INDEX
                `' . $indexName . '`
            ',
        );

        return !($queryResult === false);
    }

    /**
     * This method generates a foreign key name that is not longer than 64 characters.
     */
    public static function generateForeignKeyName(
        string $tableName,
        string $referencedTableName,
        string $referencingColumnName
    ): string {
        $foreignKeyName = 'fk_' . $tableName . '_' . $referencedTableName . '_' . $referencingColumnName;

        if (strlen($foreignKeyName) <= 56) {
            return $foreignKeyName;
        }

        // Foreign key name is too long, try to shorten it by removing `borlabs_cookie_`
        $tableName = str_replace('borlabs_cookie_', '', $tableName);
        $referencedTableName = str_replace('borlabs_cookie_', '', $referencedTableName);

        $foreignKeyName = 'fk_' . $tableName . '_' . $referencedTableName . '_' . $referencingColumnName;

        if (strlen($foreignKeyName) <= 56) {
            return $foreignKeyName;
        }

        // Foreign key name is still too long, try to shorten it by removing the table name.
        $foreignKeyName = 'fk_' . $tableName . '_' . $referencingColumnName;

        if (strlen($foreignKeyName) <= 56) {
            return $foreignKeyName;
        }

        // Remove all numbers, vowels (except i) and capital letters, then add the column name.
        return 'fk_' . preg_replace('/[0-9A-Zaeou]/', '', $tableName) . '_' . $referencingColumnName;
    }

    /**
     * This method returns the database version.
     */
    public static function getDbVersion(): string
    {
        return WpDb::getInstance()->get_var('SELECT VERSION()');
    }

    /**
     * This method checks whether the index exists in the table.
     */
    public static function indexExists(string $indexName, string $tableName): bool
    {
        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SHOW
                    INDEXES
                FROM
                    `' . $tableName . '`
                WHERE
                    `Key_name` = %s
            ',
                [
                    $indexName,
                ],
            ),
        );

        return !(empty($tableResult[0]->Key_name));
    }

    /**
     * This method checks whether the column is nullable.
     */
    public static function isColumnNullable(string $columnName, string $tableName): bool
    {
        $dbName = WpDb::getInstance()->dbname;

        // HyperDB workaround
        if (empty($dbName) && defined('DB_NAME')) {
            $dbName = DB_NAME;
        }

        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SELECT
                    `IS_NULLABLE`
                FROM
                    `information_schema`.`COLUMNS`
                WHERE
                    `TABLE_SCHEMA` = %s
                    AND
                    `TABLE_NAME` = %s
                    AND
                    `COLUMN_NAME` = %s
            ',
                [
                    $dbName,
                    $tableName,
                    $columnName,
                ],
            ),
        );

        return !(empty($tableResult[0]->IS_NULLABLE) || strtolower($tableResult[0]->IS_NULLABLE) !== 'yes');
    }

    /**
     * This method checks whether the column type matches the expected type.
     *
     * @return null|bool the function returns `null` if the column does not exist
     */
    public static function isColumnType(string $expectedColumnType, string $columnName, string $tableName): ?bool
    {
        $dbName = WpDb::getInstance()->dbname;

        // HyperDB workaround
        if (empty($dbName) && defined('DB_NAME')) {
            $dbName = DB_NAME;
        }

        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SELECT
                    `COLUMN_TYPE`
                FROM
                    `information_schema`.`COLUMNS`
                WHERE
                    `TABLE_SCHEMA` = %s
                    AND
                    `TABLE_NAME` = %s
                    AND
                    `COLUMN_NAME` = %s
            ',
                [
                    $dbName,
                    $tableName,
                    $columnName,
                ],
            ),
        );

        if (empty($tableResult[0]->COLUMN_TYPE)) {
            return null;
        }

        return !(strtolower($tableResult[0]->COLUMN_TYPE) !== strtolower($expectedColumnType));
    }

    /**
     * This method checks whether the column data type matches the expected type.
     *
     * @return null|bool the function returns `null` if the column does not exist
     */
    public static function isDataType(string $expectedType, string $columnName, string $tableName): ?bool
    {
        $dbName = WpDb::getInstance()->dbname;

        // HyperDB workaround
        if (empty($dbName) && defined('DB_NAME')) {
            $dbName = DB_NAME;
        }

        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SELECT
                    `DATA_TYPE`
                FROM
                    `information_schema`.`COLUMNS`
                WHERE
                    `TABLE_SCHEMA` = %s
                    AND
                    `TABLE_NAME` = %s
                    AND
                    `COLUMN_NAME` = %s
            ',
                [
                    $dbName,
                    $tableName,
                    $columnName,
                ],
            ),
        );

        if (empty($tableResult[0]->DATA_TYPE)) {
            return null;
        }

        return !(strtolower($tableResult[0]->DATA_TYPE) !== strtolower($expectedType));
    }

    /**
     * This method checks whether a table is empty.
     */
    public static function isTableEmpty(string $tableName): bool
    {
        $count = WpDb::getInstance()->get_var('SELECT COUNT(*) FROM `' . $tableName . '`');

        return (int) $count === 0;
    }

    /**
     * @param array<string, string> $valueMap
     */
    public static function remapValuesInColumn(string $columnName, string $tableName, array $valueMap): bool
    {
        foreach ($valueMap as $oldValue => $newValue) {
            $queryResult = WpDb::getInstance()->update($tableName, [$columnName => $newValue], [$columnName => $oldValue]);

            if ($queryResult === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method removes/drops a table.
     */
    public static function removeTable(string $tableName): bool
    {
        $tableResult = WpDb::getInstance()->query('DROP TABLE `' . $tableName . '`');

        return !($tableResult === false);
    }

    /**
     * This method renames a table.
     */
    public static function renameTable(string $newTableName, string $oldTableName): bool
    {
        $tableResult = WpDb::getInstance()->query(
            '
                RENAME TABLE
                    `' . $oldTableName . '`
                    TO
                    `' . $newTableName . '`
            ',
        );

        return !($tableResult === false);
    }

    /**
     * This method checks whether a table exists.
     */
    public static function tableExists(string $tableName): bool
    {
        $dbName = WpDb::getInstance()->dbname;

        // HyperDB workaround
        if (empty($dbName) && defined('DB_NAME')) {
            $dbName = DB_NAME;
        }

        $tableResult = WpDb::getInstance()->get_results(
            WpDb::getInstance()->prepare(
                '
                SELECT
                    `TABLE_NAME`
                FROM
                    `information_schema`.`TABLES`
                WHERE
                    `TABLE_SCHEMA` = %s
                AND
                    `TABLE_NAME` = %s
            ',
                [
                    $dbName,
                    $tableName,
                ],
            ),
        );

        return !(empty($tableResult[0]->TABLE_NAME));
    }
}
