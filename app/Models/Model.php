<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

// Mọi bảng nghiệp vụ dùng mã chuỗi tự sinh và không có created_at/updated_at.
abstract class Model extends Eloquent
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
