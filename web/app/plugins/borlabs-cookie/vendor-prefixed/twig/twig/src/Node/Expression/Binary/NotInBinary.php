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

namespace Borlabs\Cookie\Dependencies\Twig\Node\Expression\Binary;

use Borlabs\Cookie\Dependencies\Twig\Compiler;

class NotInBinary extends AbstractBinary
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('!borlabs_twig_in_filter(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('not in');
    }
}
