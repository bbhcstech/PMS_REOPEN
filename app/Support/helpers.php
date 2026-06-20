<?php

use App\Services\CompanyContext;

if (! function_exists('company_context')) {
    function company_context(): CompanyContext
    {
        return app(CompanyContext::class);
    }
}

if (! function_exists('current_company')) {
    function current_company(): ?\App\Models\Company
    {
        return company_context()->current();
    }
}
