<?php

namespace Dingo\Blueprint\Annotation;

/**
 * @Annotation
 */
class Type
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * @var mixed
     */
    public $type;

    /**
     * @array<Property>
     */
    public $properties;
}
