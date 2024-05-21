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

namespace Borlabs\Cookie\System\WordPressGlobalFunctions;

class WordpressGlobalFunctionService
{
    private WordpressGlobalFunctionUnderscoreXService $wordpressGlobalFunctionUnderscoreXService;

    public function __construct(
        WordpressGlobalFunctionUnderscoreXService $wordpressGlobalFunctionUnderscoreXService
    ) {
        $this->wordpressGlobalFunctionUnderscoreXService = $wordpressGlobalFunctionUnderscoreXService;
    }

    public function _x(string $text, string $context, string $domain = 'default'): string
    {
        return $this->wordpressGlobalFunctionUnderscoreXService->call(...func_get_args());
    }

    public function register(): void
    {
        require_once __DIR__ . '/WordPressGlobalFunctionBridge.php';
    }
}
