<?php

namespace Dingo\Blueprint\Annotation;

/**
 * @Annotation
 */
class Resource
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $method;
}
