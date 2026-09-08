<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

function testCompanyLogin($email, $password) {
    echo "TESTING LOGIN FOR EMAIL: {$email} WITH PASSWORD: {$password}\n";

    $req = Request::create('/login', 'POST', [
        'email'          => $email,
        'password'       => $password,
        'terms_accepted' => '1',
    ]);

    $session = $app = app('session')->driver();
    $req->setLaravelSession($session);
    $session->start();
    app()->instance('request', $req);

    try {
        $loginRequest = \App\Http\Requests\Auth\LoginRequest::createFrom($req);
        $loginRequest->setContainer(app())->setRedirector(app('redirect'));
        $loginRequest->validateResolved();

        $controller = new AuthenticatedSessionController();
        $response = $controller->store($loginRequest);

        echo "   - Status Code: " . $response->getStatusCode() . "\n";
        echo "   - Redirect Target URL: " . $response->getTargetUrl() . "\n";
        echo "   - Session current_company_db: " . session('current_company_db') . "\n";
        echo "   - Authenticated User: " . (\Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->name . " (ID: " . \Illuminate\Support\Facades\Auth::user()->id . ")" : "NONE") . "\n";

        if ($response->getStatusCode() === 302 && str_contains($response->getTargetUrl(), '/dashboard')) {
            echo "   ✔ LOGIN SUCCESS! Redirected to /dashboard\n";
        } else {
            echo "   ✖ LOGIN FAILED!\n";
        }
    } catch (\Throwable $e) {
        echo "   ✖ EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "------------------------------------------------------------------\n";
}

testCompanyLogin('abcd@gmail.com', '123456789');
testCompanyLogin('tech@gmail.com', '12345678');
testCompanyLogin('biriyani@gmail.com', 'sirajbiriyani123');
