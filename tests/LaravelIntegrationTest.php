<?php

namespace Dingo\Blueprint {

    use Doctrine\Common\Annotations\AnnotationReader;
    use Illuminate\Support\Collection;
    use Illuminate\Filesystem\Filesystem;
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

            $blueprint = new Blueprint(new AnnotationReader(), new Filesystem);

            $this->assertEquals(
                trim($this->simpleExample),
                $blueprint->generate($resources, 'testing', 'v1', null)
            );
        }

        public function testGetAnnotationByTypeInLaravel53x()
        {
            $resources = new Collection([new Tests\Stubs\ActivityController]);

            $blueprint = new Blueprint(new AnnotationReader, new Filesystem);

            $this->assertEquals(
                trim($this->simpleExample),
                $blueprint->generate($resources, 'testing', 'v1', null)
            );
        }
    }

}
