<?php

namespace Dingo\Blueprint\Annotation;

/**
 * @Annotation
 */
class Property
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

    /**
     * @var mixed
     */
    public $sample;
}
