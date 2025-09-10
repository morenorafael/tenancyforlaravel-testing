<?php

namespace Morenorafael\TenancyforlaravelTesting\Traits;

trait DeleteDatebase
{
    protected function DeleteDatebase()
    {
        if (file_exists(database_path('tenant_foo'))) {
            unlink(database_path('tenant_foo'));
        }
    }
}
