<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Kho extends Model
{
    protected $table = 'Kho';

    protected $primaryKey = 'Ma_kho';

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(NguoiDung::class, 'Ma_kho', 'Ma_kho');
    }

    public function tonKhos(): HasMany
    {
        return $this->hasMany(TonKho::class, 'Ma_kho', 'Ma_kho');
    }
}
