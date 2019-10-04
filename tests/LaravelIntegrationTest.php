<?php

namespace Dingo\Blueprint {

    use Illuminate\Support\Collection;
    use Illuminate\Filesystem\Filesystem;
    use Doctrine\Common\Annotations\SimpleAnnotationReader;
    use PHPUnit\Framework\TestCase;

    class LaravelIntegrationTest extends TestCase
    {
        protected $simpleExample = <<<'EOT'
FORMAT: 1A

# testing

# Activity

## Show all activities. [GET /activity]
EOT;

        public function testGetAnnotationByTypeInLaravel52x()
        {
            $resources = new Collection([new Tests\Stubs\ActivityController]);

            $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

            $this->assertEquals(trim($this->simpleExample), $blueprint->generate($resources, 'testing', 'v1', null));
        }

        public function testGetAnnotationByTypeInLaravel53x()
        {
            $resources = new Collection([new Tests\Stubs\ActivityController]);

            $blueprint = new Blueprint(new SimpleAnnotationReader, new Filesystem);

            $this->assertEquals(trim($this->simpleExample), $blueprint->generate($resources, 'testing', 'v1', null));
        }
    }

}
