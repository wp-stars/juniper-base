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

namespace Borlabs\Cookie\System\Template\CustomFunction;

use Borlabs\Cookie\Dependencies\Twig\TwigFunction;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\System\Template\Template;

final class Messages
{
    private MessageManager $messageManager;

    private Template $template;

    public function __construct(MessageManager $messageManager, Template $template)
    {
        $this->messageManager = $messageManager;
        $this->template = $template;
    }

    public function register()
    {
        $this->template->getTwig()->addFunction(
            new TwigFunction('messages', function () {
                return $this->template->getEngine()->render(
                    'message/messages.html.twig',
                    [
                        'messages' => $this->messageManager->getRaw(),
                    ],
                );
            }),
        );
    }
}
