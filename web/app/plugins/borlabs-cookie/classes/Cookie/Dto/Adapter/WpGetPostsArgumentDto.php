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

namespace Borlabs\Cookie\Dto\Adapter;

use Borlabs\Cookie\Dto\AbstractDto;

class WpGetPostsArgumentDto extends AbstractDto
{
    public int $category = 0;

    public array $exclude = [];

    public array $include = [];

    public int $numberPosts = 5;

    public string $order = 'DESC';

    public string $orderBy = 'date';

    public array $postStatus = ['publish'];

    public array $postType = ['post'];

    public bool $suppressFilters = false;
}
