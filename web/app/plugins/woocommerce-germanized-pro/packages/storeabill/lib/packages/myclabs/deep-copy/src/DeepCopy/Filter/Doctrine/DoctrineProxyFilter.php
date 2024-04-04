<?php
/**
 * @license MIT
 *
 * Modified by storeabill on 31-March-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Vendidero\StoreaBill\Vendor\DeepCopy\Filter\Doctrine;

use Vendidero\StoreaBill\Vendor\DeepCopy\Filter\Filter;

/**
 * @final
 */
class DoctrineProxyFilter implements Filter
{
    /**
     * Triggers the magic method __load() on a Doctrine Proxy class to load the
     * actual entity from the database.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
