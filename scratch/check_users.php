<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->bootstrap();
$users = App\Models\NguoiDung::select('Ten_dang_nhap', 'Ma_vai_tro', 'Ma_kho')->get();
foreach($users as $u) {
    echo $u->Ma_vai_tro . ': ' . $u->Ten_dang_nhap . ' (kho: ' . ($u->Ma_kho ?? 'null') . ")\n";
}

