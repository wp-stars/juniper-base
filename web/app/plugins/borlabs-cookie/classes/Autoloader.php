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

namespace Borlabs;

final class Autoloader
{
    private static $instance;

    public static function getInstance(): Autoloader
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array<string>
     */
    private $prefixes = [];

    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        $prefix = trim($prefix, '\\') . '\\';

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }

        if ($prepend === false) {
            array_push($this->prefixes[$prefix], $baseDir);
        } else {
            array_unshift($this->prefixes[$prefix], $baseDir);
        }
    }

    public function loadClass(string $class): bool
    {
        $prefix = $class;

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);

            $relativeClass = substr($class, $pos + 1);

            $fileLoaded = $this->loadFile($prefix, $relativeClass);

            if ($fileLoaded) {
                return true;
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    public function loadFile(string $prefix, string $relativeClass): bool
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        $relativeClass = str_replace('\\', '/', $relativeClass);

        foreach ($this->prefixes[$prefix] as $baseDir) {
            $file = $baseDir . $relativeClass . '.php';

            if ($this->requireFile($file)) {
                return true;
            }
        }

        return false;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }
}
