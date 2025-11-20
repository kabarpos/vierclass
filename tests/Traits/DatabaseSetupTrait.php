<?php

namespace Tests\Traits;

trait DatabaseSetupTrait
{
    protected function setUpDatabase(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->seed(\Database\Seeders\WhatsappSettingSeeder::class);
    }
}