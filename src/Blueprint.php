<?php

namespace Dingo\Blueprint;

use ReflectionClass;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Dingo\Blueprint\Annotation\Attributes;
use Dingo\Blueprint\Annotation\Attribute;
use Dingo\Blueprint\Annotation\Member;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Parameter;

class Blueprint
{
    /**
     * Simple annotation reader instance.
     *
     * @var \Doctrine\Common\Annotations\SimpleAnnotationReader
     */
    protected $reader;

    /**
     * Filesytsem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Include path for documentation files.
     *
     * @var string
     */
    protected $includePath;

    /**
     * Create a new generator instance.
     *
     * @param \Doctrine\Common\Annotations\SimpleAnnotationReader $reader
     * @param \Illuminate\Filesystem\Filesystem                   $files
     *
     * @return void
     */
    public function __construct(SimpleAnnotationReader $reader, Filesystem $files)
    {
        $this->reader = $reader;
        $this->files = $files;

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
     * @param \Illuminate\Support\Collection $controllers
     * @param string                         $name
     * @param string                         $version
     * @param string                         $includePath
     *
     * @return bool
     */
    public function generate(Collection $controllers, $name, $version, $includePath = null)
    {
        $this->includePath = $includePath;

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
                    if (!$actions->contains($method)) {
                        $annotations = $this->fillAnnotations($annotations, $method);
                        $actions->push(new Action($method, new Collection($annotations)));
                    }
                }
            }
            $annotations = new Collection($this->reader->getClassAnnotations($controller));

            return new Resource($controller->getName(), $controller, $annotations, $actions);
        });

        $contents = $this->generateContentsFromResources($resources, $name);

        $this->includePath = null;

        return $contents;
    }

    /**
     * Add the parameters and attributes objects to annotations if needed
     *
     * @param array  $annotations the annotations to fill
     * @param object $method      the method we are working on
     *
     * @return array
     */
    private function fillAnnotations(array $annotations, $method)
    {
        $controllerName = substr($method->class, strrpos($method->class, '\\'));
        if ($controllerName != '\\OAuthController') {
            $rulesClass = 'App\\Validators\\Rules' . $controllerName;
            $parameters = $this->extractParams($this->getMethodUri($annotations));

            $annotations[] = $this->getAttributes($rulesClass::$rules[$method->name], $parameters);
            $annotations[] = $this->getParameters($rulesClass::$rules[$method->name], $parameters);
        }
        return $annotations;
    }

    /**
     * Get the URI of a method from it's annotations
     *
     * @param array $annotations
     *
     * @return string
     */
    private function getMethodUri(array $annotations)
    {
        return array_first($annotations, function ($key, $annotation) {
            $type = 'Dingo\\Blueprint\\Annotation\\Method\\Method';
            return is_object($annotation) ? $annotation instanceof $type : $key instanceof $type;
        })->uri;
    }

    /**
     * Gets a list of all parameters name from mutiple arrays extracted from the uri
     *
     * @param array $uriParams
     *
     * @return array
     */
    private function extractParams($uri) {
        preg_match_all("/{(.*?)}/", $uri, $matches);
        return $this->parseParams($matches[1]);
    }

    /**
     * Parse the params as they are in the uri into correct array
     *
     * @param array $uriParams
     *
     * @return array
     */ 
    private function parseParams($uriParams)
    {
        $params    = [];

        foreach ($uriParams as $uriParam) {
            ltrim($uriParam, '?');
            $uriParamExploded = explode(',', $uriParam);
            foreach ($uriParamExploded as $param) {
                $params[] = $param;
            }
        }
        return $params;
    }

    /**
     * Transforms a laravel validation array in Attribute for dingo blueprint
     *
     * @param array $rules
     *
     * @return array
     */
    private function getAttributes($rules, $parameters)
    {
        $attributes = new Attributes();

        foreach ($rules as $identifier => $attrInfos) {
            if (in_array($identifier, $parameters)) {
                continue;
            }
            $attribute = $this->parseInfos(new Attribute(), $attrInfos);
            $attribute->identifier = $identifier;

            $attributes->value[] = $attribute;
        }
        return $attributes->value ? $attributes : null;
    }

    /**
     * Transforms a laravel validation array in Parameter for dingo blueprint
     *
     * @param array $rules
     *
     * @return array
     */
    private function getParameters($rules, $params)
    {
        $parameters = new Parameters();

        foreach ($rules as $identifier => $paramInfos) {
            if (!in_array($identifier, $params)) {
                continue;
            }
            $parameter = $this->parseInfos(new Parameter(), $paramInfos);
            $parameter->identifier = $identifier;

            $parameters->value[] = $parameter;
        }
        return $parameters->value ? $parameters : null;
    }

    /**
     * Parse the validation array to fill a parameter or an attribute
     *
     * @param Parameter|Attribute $toFill
     * @param array               $infos
     *
     * @return Parameter|Attribute
     */
    private function parseInfos($toFill, $infos)
    {
        $toFill->description = $infos['description'];

        $propertiesExploded = explode('|', $infos['rules']);
        foreach ($propertiesExploded as $property) {
            switch ($property) {
                case 'numeric':
                case 'integer':
                    $toFill->type = 'number';
                    continue 2;
                case 'array':
                    $toFill->type = 'object';
                    continue 2;
                case 'present':
                case 'required':
                    $toFill->required = true;
                    continue 2;
            }
            $tofill = $this->parseComplexProperties($toFill, $property);
        }
        return $toFill;
    }

    /**
     * Parse the property to find enum or defaults
     * 
     * @param Parameter|Attribute $toFill
     * @param string              $property
     *
     * @return Parameter|Attribute
     */
    private function parseComplexProperties($toFill, $property)
    {
        $propertyExploded = explode(':', $property);
        if (count($propertyExploded) > 1) {
            switch ($propertyExploded[0]) {
                case 'default':
                    $toFill->default = $propertyExploded[1];
                    break;
                case 'in':
                    $members = explode(',', $propertyExploded[1]);
                    foreach ($members as $identifier) {
                        $member = new Member();
                        $member->identifier = $identifier;
                        $toFill->members[] = $member;
                    }
                    break;
            }
        }
        return $toFill;
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
        $contents .= $this->getHost();
        $contents .= $this->line(2);
        $contents .= sprintf('# %s', $name);
        $contents .= $this->line(2);

        $resources->each(function ($resource) use (&$contents) {
            if ($resource->getActions()->isEmpty()) {
                return;
            }

            $contents .= $resource->getDefinition();

            if ($description = $resource->getDescription()) {
                $contents .= $this->line();
                $contents .= $description;
            }

            if (($parameters = $resource->getParameters()) && ! $parameters->isEmpty()) {
                $this->appendParameters($contents, $parameters);
            }

            $resource->getActions()->each(function ($action) use (&$contents, $resource) {
                $contents .= $this->line(2);
                $contents .= $action->getDefinition();

                if ($description = $action->getDescription()) {
                    $contents .= $this->line();
                    $contents .= $description;
                }

                if (($attributes = $action->getAttributes()) && ! $attributes->isEmpty()) {
                    $this->appendAttributes($contents, $attributes);
                }

                if (($parameters = $action->getParameters()) && ! $parameters->isEmpty()) {
                    $this->appendParameters($contents, $parameters);
                }

                if ($request = $action->getRequest()) {
                    $this->appendRequest($contents, $request, $resource);
                }

                if ($response = $action->getResponse()) {
                    $this->appendResponse($contents, $response, $resource);
                }

                if ($transaction = $action->getTransaction()) {
                    foreach ($transaction->value as $value) {
                        if ($value instanceof Annotation\Request) {
                            $this->appendRequest($contents, $value, $resource);
                        } elseif ($value instanceof Annotation\Response) {
                            $this->appendResponse($contents, $value, $resource);
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
     * Append the attributes subsection to a resource or action.
     *
     * @param string                         $contents
     * @param \Illuminate\Support\Collection $attributes
     * @param int                            $indent
     *
     * @return void
     */
    protected function appendAttributes(&$contents, Collection $attributes, $indent = 0)
    {
        $this->appendSection($contents, 'Attributes', $indent);

        $attributes->each(function ($attribute) use (&$contents, $indent) {
            $explodedIdentifier = explode('.', $attribute->identifier);
            $indent += count($explodedIdentifier);
            $contents .= $this->line();
            $contents .= $this->tab($indent);
            $contents .= sprintf('+ %s', $explodedIdentifier[count($explodedIdentifier) - 1]);

            if ($attribute->sample) {
                $contents .= sprintf(': %s', $attribute->sample);
            }

            $contents .= sprintf(
                ' (%s, %s) - %s',
                $attribute->members ? sprintf('enum[%s]', $attribute->type) : $attribute->type,
                $attribute->required ? 'required' : 'optional',
                $attribute->description
            );

            if (isset($attribute->default)) {
                $this->appendSection($contents, sprintf('Default: %s', $attribute->default), $indent + 1, 1);
            }

            if (isset($attribute->members)) {
                $this->appendSection($contents, 'Members', $indent + 1, 1);
                foreach ($attribute->members as $member) {
                    $this->appendSection($contents, sprintf('`%s` - %s', $member->identifier, $member->description), $indent + 2, 1);
                }
            }
        });

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
            $contents .= sprintf('+ %s', $parameter->identifier);

            if ($parameter->example) {
                $contents .= sprintf(': %s', $parameter->example);
            }

            $contents .= sprintf(
                ' (%s, %s) - %s',
                $parameter->members ? sprintf('enum[%s]', $parameter->type) : $parameter->type,
                $parameter->required ? 'required' : 'optional',
                $parameter->description
            );

            if (isset($parameter->default)) {
                $this->appendSection($contents, sprintf('Default: %s', $parameter->default), 2, 1);
            }

            if (isset($parameter->members)) {
                $this->appendSection($contents, 'Members', 2, 1);
                foreach ($parameter->members as $member) {
                    $this->appendSection($contents, sprintf('`%s` - %s', $member->identifier, $member->description), 3, 1);
                }
            }
        });
    }

    /**
     * Append a response subsection to an action.
     *
     * @param string                               $contents
     * @param \Dingo\Blueprint\Annotation\Response $response
     * @param \Dingo\Blueprint\Resource            $resource
     *
     * @return void
     */
    protected function appendResponse(&$contents, Annotation\Response $response, Resource $resource)
    {
        $this->appendSection($contents, sprintf('Response %s', $response->statusCode));

        if (isset($response->contentType)) {
            $contents .= ' ('.$response->contentType.')';
        }

        if (! empty($response->headers) || $resource->hasResponseHeaders()) {
            $this->appendHeaders($contents, array_merge($resource->getResponseHeaders(), $response->headers));
        }

        if (isset($response->attributes)) {
            $this->appendAttributes($contents, collect($response->attributes), 1);
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
     * @param \Dingo\Blueprint\Resource           $resource
     *
     * @return void
     */
    protected function appendRequest(&$contents, $request, Resource $resource)
    {
        $this->appendSection($contents, 'Request');

        if (isset($request->identifier)) {
            $contents .= ' '.$request->identifier;
        }

        $contents .= ' ('.$request->contentType.')';

        if (!isset($request->headers['Authorization'])) {
            $request->headers['Authorization'] = 'OAuth: oauth_consumer_key={consumer_key},oauth_signature={consumer_secret}&{user_secret},oauth_signature_method=PLAINTEXT,oauth_nonce={random_string},oauth_timestamp={current_timestamp},oauth_token={user_token}';
        }
        if (! empty($request->headers) || $resource->hasRequestHeaders()) {
            $this->appendHeaders($contents, array_merge($resource->getRequestHeaders(), $request->headers));
        }

        if (isset($request->attributes)) {
            $this->appendAttributes($contents, collect($request->attributes), 1);
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
     * @param array  $headers
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
        if (is_string($body) && Str::startsWith($body, ['json', 'file'])) {
            list($type, $path) = explode(':', $body);

            if (! Str::endsWith($path, '.json') && $type == 'json') {
                $path .= '.json';
            }

            $body = $this->files->get($this->includePath.'/'.$path);

            json_decode($body);

            if (json_last_error() == JSON_ERROR_NONE) {
                return $body;
            }
        }

        if (strpos($contentType, 'application/json') === 0) {
            return json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
        return str_repeat('    ', $repeat);
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

    private function getHost()
    {
        if (class_exists('\Illuminate\Config\Repository')) {
            return $this->line(1) . 'HOST: https://' . app('config')->get('api.domain') . '/' . app('config')->get('api.prefix');
        }
    }
}
