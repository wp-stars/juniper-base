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

namespace Borlabs\Cookie\ApiClient\Transformer;

use Borlabs\Cookie\Dto\News\NewsDto;
use DateTime;

final class NewsTransformer
{
    public function toDto(object $news): NewsDto
    {
        return new NewsDto(
            $news->id,
            $news->language,
            $news->title,
            $news->message,
            (new DateTime())->setTimestamp($news->timestamp),
        );
    }
}
