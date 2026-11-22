<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();

function testRoute($app, $kernel, $uri, $user) {
    $req = \Illuminate\Http\Request::create($uri, 'GET');
    $app->instance('request', $req);
    $session = $app->make('session')->driver();
    $session->start();
    $req->setLaravelSession($session);
    \Illuminate\Support\Facades\Auth::login($user);
    $req->setUserResolver(fn() => $user);
    $res = $kernel->handle($req);
    echo $uri . " -> Status: " . $res->getStatusCode() . "\n";
    if ($res->getStatusCode() >= 400) {
        if (isset($res->exception) && $res->exception) {
            echo "Exception: " . $res->exception->getMessage() . "\n";
        }
    }
}

testRoute($app, $kernel, '/kho/ton-kho', $user);
testRoute($app, $kernel, '/kho/don-dat-hang', $user);
testRoute($app, $kernel, '/kho/de-xuat', $user);
testRoute($app, $kernel, '/kho/de-xuat/tao', $user);

$userKt = \App\Models\NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
testRoute($app, $kernel, '/ke-toan/de-xuat', $userKt);

