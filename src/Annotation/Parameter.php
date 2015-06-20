<?php

namespace Dingo\Blueprint\Annotation;

/**
 * @Annotation
 */
class Parameter
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
     * @var bool
     */
    public $required = false;

    /**
     * @var string
     */
    public $description;

    /**
     * @var mixed
     */
    public $default;
}
