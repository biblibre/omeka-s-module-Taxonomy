<?php

namespace Taxonomy\DataType\Resource;

use Omeka\DataType\Resource\AbstractResource as OmekaAbstractResource;
use Omeka\DataType\ValueAnnotatingInterface;

if (interface_exists(ValueAnnotatingInterface::class)) {
    abstract class AbstractResource extends OmekaAbstractResource implements ValueAnnotatingInterface
    {
    }
} else {
    abstract class AbstractResource extends OmekaAbstractResource
    {
    }
}
