<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class KhachHang extends Model
{
    protected $table = 'Khach_hang';

    protected $primaryKey = 'Ma_khach_hang';

    public function donHangBans(): HasMany
    {
        return $this->hasMany(DonHangBan::class, 'Ma_khach_hang', 'Ma_khach_hang');
    }
}
