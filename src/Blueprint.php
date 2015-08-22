<?php

namespace Dingo\Blueprint;

use ReflectionClass;
use RuntimeException;
use Dingo\Blueprint\Annotation;
use Illuminate\Support\Collection;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

class Blueprint
{
    /**
     * Simple annotation reader instance.
     *
     * @var \Doctrine\Common\Annotations\SimpleAnnotationReader
     */
    protected $reader;

    /**
     * Create a new generator instance.
     *
     * @param \Doctrine\Common\Annotations\SimpleAnnotationReader $reader
     *
     * @return void
     */
    public function __construct(SimpleAnnotationReader $reader)
    {
        $this->reader = $reader;

        $this->registerAnnotationLoader();
    }

    /**
     * Register the annotation loader.
     *
     * @return void
     */
    protected function registerAnnotationLoader()
    {
        $this->reader->addNamespace('Dingo\\Blueprint\\Annotation');
        $this->reader->addNamespace('Dingo\\Blueprint\\Annotation\\Method');

        AnnotationRegistry::registerLoader(function ($class) {
            $path = __DIR__.'/'.str_replace(['Dingo\\Blueprint\\', '\\'], ['', DIRECTORY_SEPARATOR], $class).'.php';

            if (file_exists($path)) {
                require_once $path;

                return true;
            }
        });
    }

    /**
     * Generate documentation with the name and version.
     *
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public function generate(Collection $controllers, $name, $version)
    {
        $resources = $controllers->map(function ($controller) use ($version) {
            $controller = $controller instanceof ReflectionClass ? $controller : new ReflectionClass($controller);

            $actions = new Collection;

            // Spin through all the methods on the controller and compare the version
            // annotation (if supplied) with the version given for the generation.
            // We'll also build up an array of actions on each resource.
            foreach ($controller->getMethods() as $method) {
                if ($versionAnnotation = $this->reader->getMethodAnnotation($method, Annotation\Versions::class)) {
                    if (! in_array($version, $versionAnnotation->value)) {
                        continue;
                    }
                }

                if ($annotations = $this->reader->getMethodAnnotations($method)) {
                    if (! $actions->contains($method)) {
                        $actions->push(new Action($method, new Collection($annotations)));
                    }
                }
            }

            $annotations = new Collection($this->reader->getClassAnnotations($controller));

            return new Resource($controller->getName(), $controller, $annotations, $actions);
        });

        return $this->generateContentsFromResources($resources, $name);
    }

    /**
     * Generate the documentation contents from the resources collection.
     *
     * @param \Illuminate\Support\Collection $resources
     * @param string                         $name
     *
     * @return string
     */
    protected function generateContentsFromResources(Collection $resources, $name)
    {
        $contents = '';

        $contents .= $this->getFormat();
        $contents .= $this->line(2);
        $contents .= sprintf('# %s', $name);
        $contents .= $this->line(2);

        $resources->each(function ($resource) use (&$contents) {
            $contents .= $resource->getDefinition();

            if ($description = $resource->getDescription()) {
                $contents .= $this->line();
                $contents .= $description;
            }

            if (($parameters = $resource->getParameters()) && ! $parameters->isEmpty()) {
                $this->appendParameters($contents, $parameters);
            }

            $resource->getActions()->each(function ($action) use (&$contents) {
                $contents .= $this->line(2);
                $contents .= $action->getDefinition();

                if ($description = $action->getDescription()) {
                    $contents .= $this->line();
                    $contents .= $description;
                }

                if (($parameters = $action->getParameters()) && ! $parameters->isEmpty()) {
                    $this->appendParameters($contents, $parameters);
                }

                if ($request = $action->getRequest()) {
                    $this->appendRequest($contents, $request);
                }

                if ($response = $action->getResponse()) {
                    $this->appendResponse($contents, $response);
                }

                if ($transaction = $action->getTransaction()) {
                    foreach ($transaction->value as $value) {
                        if ($value instanceof Annotation\Request) {
                            $this->appendRequest($contents, $value);
                        } elseif ($value instanceof Annotation\Response) {
                            $this->appendResponse($contents, $value);
                        } else {
                            throw new RuntimeException('Unsupported annotation type given in transaction.');
                        }
                    }
                }
            });

            $contents .= $this->line(2);
        });

        return stripslashes(trim($contents));
    }

    /**
     * Append the parameters subsection to a resource or action.
     *
     * @param string                         $contents
     * @param \Illuminate\Support\Collection $parameters
     *
     * @return void
     */
    protected function appendParameters(&$contents, Collection $parameters)
    {
        $this->appendSection($contents, 'Parameters');

        $parameters->each(function ($parameter) use (&$contents) {
            $contents .= $this->line();
            $contents .= $this->tab();
            $contents .= sprintf(
                '+ %s (%s, %s) - %s',
                $parameter->identifier,
                $parameter->type,
                $parameter->required ? 'required' : 'optional',
                $parameter->description
            );

            if (isset($parameter->default)) {
                $this->appendSection($contents, sprintf('Default: %s', $parameter->default), 2, 1);
            }
        });
    }

    /**
     * Append a response subsection to an action.
     *
     * @param string                               $contents
     * @param \Dingo\Blueprint\Annotation\Response $response
     *
     * @return void
     */
    protected function appendResponse(&$contents, Annotation\Response $response)
    {
        $this->appendSection($contents, sprintf('Response %s', $response->statusCode));

        if (isset($response->contentType)) {
            $contents .= ' ('.$response->contentType.')';
        }

        if (! empty($request->headers)) {
            $this->appendHeaders($contents, $request->headers);
        }

        if (isset($response->body)) {
            $this->appendBody($contents, $this->prepareBody($response->body, $response->contentType));
        }
    }

    /**
     * Append a request subsection to an action.
     *
     * @param string                              $contents
     * @param \Dingo\Blueprint\Annotation\Request $request
     *
     * @return void
     */
    protected function appendRequest(&$contents, $request)
    {
        $this->appendSection($contents, 'Request');

        if (isset($request->identifier)) {
            $contents .= ' '.$request->identifier;
        }

        $contents .= ' ('.$request->contentType.')';

        if (! empty($request->headers)) {
            $this->appendHeaders($contents, $request->headers);
        }

        if (isset($request->body)) {
            $this->appendBody($contents, $this->prepareBody($request->body, $request->contentType));
        }
    }

    /**
     * Append a body subsection to an action.
     *
     * @param string $contents
     * @param string $body
     *
     * @return void
     */
    protected function appendBody(&$contents, $body)
    {
        $this->appendSection($contents, 'Body', 1, 1);

        $contents .= $this->line(2);

        $line = strtok($body, "\r\n");

        while ($line !== false) {
            $contents .= $this->tab(3).$line;

            $line = strtok("\r\n");

            if ($line !== false) {
                $contents .= $this->line();
            }
        }
    }

    /**
     * Append a headers subsection to an action.
     *
     * @param string $contents
     * @param array  $response
     *
     * @return void
     */
    protected function appendHeaders(&$contents, array $headers)
    {
        $this->appendSection($contents, 'Headers', 1, 1);

        $contents .= $this->line();

        foreach ($headers as $header => $value) {
            $contents .= $this->line().$this->tab(3).sprintf('%s: %s', $header, $value);
        }
    }

    /**
     * Append a subsection to an action.
     *
     * @param string $contents
     * @param string $name
     * @param int    $indent
     * @param int    $lines
     *
     * @return void
     */
    protected function appendSection(&$contents, $name, $indent = 0, $lines = 2)
    {
        $contents .= $this->line($lines);
        $contents .= $this->tab($indent);
        $contents .= '+ '.$name;
    }

    /**
     * Prepare a body.
     *
     * @param string $body
     * @param string $contentType
     *
     * @return string
     */
    protected function prepareBody($body, $contentType)
    {
        if ($contentType == 'application/json') {
            return json_encode($body, JSON_PRETTY_PRINT);
        }

        return $body;
    }

    /**
     * Create a new line character.
     *
     * @param int $repeat
     *
     * @return string
     */
    protected function line($repeat = 1)
    {
        return str_repeat("\n", $repeat);
    }

    /**
     * Create a tab character.
     *
     * @param int $repeat
     *
     * @return string
     */
    protected function tab($repeat = 1)
    {
        return str_repeat("    ", $repeat);
    }

    /**
     * Get the API Blueprint format.
     *
     * @return string
     */
    protected function getFormat()
    {
        return 'FORMAT: 1A';
    }
}
