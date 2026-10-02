<?php

namespace App\Http\Controllers;

use App\Support\CountryPhone as BaseCountryPhone;

/**
 * Controller-namespace proxy for App\Support\CountryPhone.
 * Ensures any unqualified references to CountryPhone within the App\Http\Controllers namespace
 * resolve seamlessly with all methods intact.
 */
class CountryPhone extends BaseCountryPhone
{
}
