<?php

namespace Morenorafael\TenancyforlaravelTesting;

use App\Models\Tenant;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class TenantDuskTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->resetTenantSchema('foo');
        $this->initializeTenancy();
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }

    public function initializeTenancy()
    {
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }

        Browser::macro('actingAs', function (User $user) {
            return $this->visit(route_tenant('login'))
                ->waitFor('@email')
                ->type('@email', $user->email)
                ->waitFor('@password')
                ->type('@password', 'password')
                ->waitFor('@submit')
                ->press('@submit')
                ->pause(1000);
        });

        $tenant = Tenant::create(['id' => 'foo']);
        $tenant->domains()->create(['domain' => 'foo' . config('app.base_url')]);

        tenancy()->initialize($tenant);
    }

    public function resetTenantSchema(string $tenantId)
    {
        $tenantId = "tenant_{$tenantId}";

        try {
            DB::statement("DROP SCHEMA IF EXISTS \"$tenantId\" CASCADE");
        } catch (\Exception $e) {
            dump('Error reseteando el esquema del tenant: ' . $e->getMessage());
        }
    }

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--user-data-dir=' . sys_get_temp_dir() . '/chrome-' . uniqid(),
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
}
