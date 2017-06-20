<?php

namespace Dingo\Blueprint\Tests\Stubs;

/**
 * @Group("Activity")
 * @Versions({"v1"})
 * @Resource("Activity")
 */
class ActivityController
{
    /**
     * Show all activities.
     *
     * @Get("activity")
     * @Versions({"v1"})
     */
    public function getIndex()
    {
    }
}
