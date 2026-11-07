<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SanPham extends Model
{
    protected $table = 'San_pham';

    protected $primaryKey = 'Ma_san_pham';

    protected $casts = [
        'Gia_von_binh_quan' => 'decimal:2',
    ];

    public function los(): HasMany
    {
        return $this->hasMany(Lo::class, 'Ma_san_pham', 'Ma_san_pham');
    }
}
