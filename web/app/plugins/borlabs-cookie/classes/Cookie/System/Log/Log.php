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

namespace Borlabs\Cookie\System\Log;

use Borlabs\Cookie\Enum\Log\LogLevelEnum;
use Borlabs\Cookie\Exception\GenericException;
use Borlabs\Cookie\Support\Formatter;
use Borlabs\Cookie\System\Config\PluginConfig;

class Log
{
    private DatabaseLog $databaseLog;

    private FileLog $fileLog;

    private MailLog $mailLog;

    private PluginConfig $pluginConfig;

    private string $processId;

    public function __construct(
        DatabaseLog $databaseLog,
        FileLog $fileLog,
        MailLog $mailLog,
        PluginConfig $pluginConfig
    ) {
        $this->databaseLog = $databaseLog;
        $this->fileLog = $fileLog;
        $this->mailLog = $mailLog;
        $this->pluginConfig = $pluginConfig;

        $this->processId = uniqid();
    }

    public function alert(string $message, ?array $context = null): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->addLog(LogLevelEnum::ALERT(), $message, $context, $backtrace);
    }

    public function critical(string $message, ?array $context = null): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->addLog(LogLevelEnum::CRITICAL(), $message, $context, $backtrace);
    }

    public function debug(string $message, ?array $context = null, bool $withBacktrace = false): void
    {
        if (!$this->pluginConfig->get()->enableDebugLogging) {
            return;
        }

        $backtrace = null;

        if ($withBacktrace) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        $this->addLog(LogLevelEnum::DEBUG(), $message, $context, $backtrace);
    }

    public function emergency(string $message, ?array $context = null): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->addLog(LogLevelEnum::EMERGENCY(), $message, $context, $backtrace);
    }

    public function error(string $message, ?array $context = null): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->addLog(LogLevelEnum::ERROR(), $message, $context, $backtrace);
    }

    public function info(string $message, ?array $context = null): void
    {
        $this->addLog(LogLevelEnum::INFO(), $message, $context);
    }

    public function notice(string $message, ?array $context = null): void
    {
        $this->addLog(LogLevelEnum::NOTICE(), $message, $context);
    }

    public function warning(string $message, ?array $context = null): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->addLog(LogLevelEnum::WARNING(), $message, $context, $backtrace);
    }

    private function addLog(LogLevelEnum $level, string $message, ?array $context = null, ?array $backtrace = null): void
    {
        if ($context !== null) {
            $message = Formatter::interpolate($message, $context);
        }

        try {
            $this->databaseLog->addLogEntry($this->processId, $level, $message, $context, $backtrace);
        } catch (GenericException $e) {
            try {
                $this->fileLog->addLogEntry($this->processId, $level, $message, $context, $backtrace);
            } catch (GenericException $e) {
                try {
                    $this->mailLog->addLogEntry($this->processId, $level, $message, $context, $backtrace);
                } catch (GenericException $e) {
                    // Oh boi...
                    error_log('Borlabs Cookie: ' . $e->getMessage());
                }
            }
        }
    }
}
