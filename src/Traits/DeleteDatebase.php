<?php

namespace Morenorafael\TenancyforlaravelTesting\Traits;

trait DeleteDatebase
{
    protected function deleteDatebase()
    {
        if (file_exists(database_path('tenant_foo'))) {
            unlink(database_path('tenant_foo'));
        }
    }
}
