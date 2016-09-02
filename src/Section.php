<?php

namespace Dingo\Blueprint;

use Illuminate\Support\Collection;

abstract class Section
{
    /**
     * Get an annotation by its type.
     *
     * @param string $type
     *
     * @return mixed
     */
    protected function getAnnotationByType($type)
    {
        return array_first($this->annotations, function ($key, $annotation) use ($type) {
            $type = sprintf('Dingo\\Blueprint\\Annotation\\%s', $type);

            return is_object($annotation) ? $annotation instanceof $type : $key instanceof $type;
        });
    }

    /**
     * Get a sections parameter annotations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getParameters()
    {
        $parameters = new Collection;

        if ($annotation = $this->getAnnotationByType('Parameters')) {
            foreach ($annotation->value as $parameter) {
                $parameters[] = $parameter;
            }
        }

        return $parameters;
    }

    /**
     * Get a sections attribute annotations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttributes()
    {
        $attributes = new Collection;

        if ($annotation = $this->getAnnotationByType('Attributes')) {
            foreach ($annotation->value as $attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
