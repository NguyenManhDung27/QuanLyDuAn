<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class VaiTro extends Model
{
    protected $table = 'Vai_tro';

    protected $primaryKey = 'Ma_vai_tro';

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(NguoiDung::class, 'Ma_vai_tro', 'Ma_vai_tro');
    }
}
