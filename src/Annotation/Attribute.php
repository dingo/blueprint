<?php

namespace Dingo\Blueprint\Annotation;

/**
 * @Annotation
 */
class Attribute
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $type = 'string';

    /**
     * @var string
     */
    public $description;
}
