<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

abstract class TenantTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->resetTenantDatabaseFile('foo');
        $this->initializeTenancy();
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();

        if (file_exists(database_path('tenant_foo'))) {
            unlink(database_path('tenant_foo'));
        }
    }

    public function initializeTenancy(): void
    {
        $tenant = Tenant::create(['id' => 'foo']);
        $tenant->domains()->create(['domain' => 'foo.' . config('app.base_url')]);

        tenancy()->initialize($tenant);
    }

    public function resetTenantSchema(string $tenantId): void
    {
        $tenantId = "tenant_{$tenantId}";

        try {
            DB::statement("DROP SCHEMA IF EXISTS \"$tenantId\" CASCADE");
        } catch (\Exception $e) {
            // dump('Error reseteando el esquema del tenant: '.$e->getMessage());
        }
    }

    public function resetTenantDatabaseFile(string $tenantId): void
    {
        $tenantId = "tenant_{$tenantId}";

        $files = glob(database_path('tenant_*'));

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
