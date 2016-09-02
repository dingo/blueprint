<?php

namespace {
    $arrayFirstVersion = '5.2';
}

namespace Dingo\Blueprint {

    use PHPUnit_Framework_TestCase;
    use Illuminate\Support\Collection;
    use Illuminate\Filesystem\Filesystem;
    use Doctrine\Common\Annotations\SimpleAnnotationReader;

    function array_first($array, callable $callback = null, $default = null)
    {
        global $arrayFirstVersion;
        switch ($arrayFirstVersion) {
            case '5.2':
                if (is_null($callback)) {
                    return empty($array) ? value($default) : reset($array);
                }
                foreach ($array as $key => $value) {
                    if (call_user_func($callback, $key, $value)) {
                        return $value;
                    }
                }
                break;
            case '5.3':
                if (is_null($callback)) {
                    if (empty($array)) {
                        return value($default);
                    }

                    foreach ($array as $item) {
                        return $item;
                    }
                }
                foreach ($array as $key => $value) {
                    if (call_user_func($callback, $value, $key)) {
                        return $value;
                    }
                }
                break;
        }

        return value($default);
    }

    class LaravelIntegrationTest extends PHPUnit_Framework_TestCase
    {
        protected $simpleExample = <<<'EOT'
FORMAT: 1A

# testing

# Activity

## Show all activities. [GET /activity]
EOT;

        public function testGetAnnotationByTypeInLaravel52x()
        {
            global $arrayFirstVersion;
            $arrayFirstVersion = '5.2';

            $resources = new Collection([new Tests\Stubs\ActivityController]);

            $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

            $this->assertEquals(trim($this->simpleExample), $blueprint->generate($resources, 'testing', 'v1', null));
        }

        public function testGetAnnotationByTypeInLaravel53x()
        {
            global $arrayFirstVersion;
            $arrayFirstVersion = '5.3';

            $resources = new Collection([new Tests\Stubs\ActivityController]);

            $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

            $this->assertEquals(trim($this->simpleExample), $blueprint->generate($resources, 'testing', 'v1', null));
        }
    }

}
