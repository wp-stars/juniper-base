<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by borlabs on 31-January-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Borlabs\Cookie\Dependencies\Twig\Node\Expression;

use Borlabs\Cookie\Dependencies\Twig\Compiler;

class VariadicExpression extends ArrayExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler->raw('...');

        parent::compile($compiler);
    }
}
