<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\DatabaseSetupTrait;

abstract class TestCase extends BaseTestCase
{
    use DatabaseSetupTrait;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Automatically setup database if RefreshDatabase trait is used
        if (in_array(RefreshDatabase::class, class_uses_recursive($this))) {
            $this->setUpDatabase();
        }
    }
}
