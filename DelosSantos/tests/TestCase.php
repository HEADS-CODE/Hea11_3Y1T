<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $compiledViews = sys_get_temp_dir().DIRECTORY_SEPARATOR.'puihaha-laravel-test-views';

        if (! is_dir($compiledViews)) {
            mkdir($compiledViews, 0777, true);
        }

        $app['config']->set('view.compiled', $compiledViews);

        return $app;
    }
}
