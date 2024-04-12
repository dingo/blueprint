<?php

namespace Dingo\Blueprint\Tests\Stubs;

use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Method\Get;

/**
 * @Resource("Activity")
 */
class ActivityController
{
    /**
     * Show all activities.
     *
     * @Get("activity")
     */
    public function getIndex()
    {
    }
}
