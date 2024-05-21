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

namespace Borlabs\Cookie\Dto\Attachment;

use Borlabs\Cookie\Dto\AbstractDto;

final class AttachmentDto extends AbstractDto
{
    public string $downloadUrl;

    public string $id;

    public function __construct(
        string $id,
        string $downloadUrl
    ) {
        $this->downloadUrl = $downloadUrl;
        $this->id = $id;
    }
}
