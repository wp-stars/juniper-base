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

namespace Borlabs\Cookie\System\WordPressFrontendDriver;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\System\LocalScanner\ScanRequestService;
use Borlabs\Cookie\System\ScriptBlocker\ScriptBlockerManager;
use Borlabs\Cookie\System\StyleBlocker\StyleBlockerManager;

final class OutputBufferManager
{
    private $buffer = '';

    private bool $isActive = false;

    private ScanRequestService $scanRequestService;

    private ScriptBlockerManager $scriptBlockerManager;

    private StyleBlockerManager $styleBlockerManager;

    private WpFunction $wpFunction;

    public function __construct(
        ScanRequestService $scanRequestService,
        ScriptBlockerManager $scriptBlockerManager,
        StyleBlockerManager $styleBlockerManager,
        WpFunction $wpFunction
    ) {
        $this->scanRequestService = $scanRequestService;
        $this->scriptBlockerManager = $scriptBlockerManager;
        $this->styleBlockerManager = $styleBlockerManager;
        $this->wpFunction = $wpFunction;
    }

    public function &getBuffer(): string
    {
        return $this->buffer;
    }

    public function endBuffering(): bool
    {
        if ($this->isActive === true) {
            $this->buffer = ob_get_contents();
            ob_end_clean();
            $this->isActive = false;

            return true;
        }

        return false;
    }

    public function isBufferingActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Output the buffer via echo and clear the buffer.
     */
    public function outputBuffer(): void
    {
        echo $this->buffer;
        unset($this->buffer);
    }

    public function setBuffer(string $buffer): void
    {
        $this->buffer = $buffer;
    }

    public function startBuffering(): bool
    {
        if (
            $this->scanRequestService->isScanRequest() === false
            && $this->scriptBlockerManager->hasScriptBlockers() === false
            && $this->styleBlockerManager->hasStyleBlockers() === false
        ) {
            return false;
        }

        // Allow to disable the buffering when a Page Builder is active
        $this->isActive = $this->wpFunction->applyFilter('borlabsCookie/outputBufferManager/status', true);

        if ($this->isActive) {
            ob_start();

            return true;
        }

        return false;
    }
}
