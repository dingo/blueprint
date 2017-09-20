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
     * @var bool
     */
    public $required = false;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $default;

    /**
     * @var mixed
     */
    public $sample;

    /**
     * @array<Member>
     */
    public $members;
}
