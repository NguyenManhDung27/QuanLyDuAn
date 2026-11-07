<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDonDatHang extends ChiTietModel
{
    protected $table = 'Chi_tiet_don_dat_hang';

    protected $primaryKey = 'Ma_don_dat_hang';

    protected array $khoaGhep = ['Ma_don_dat_hang', 'Ma_san_pham'];

    public function donDatHang(): BelongsTo
    {
        return $this->belongsTo(DonDatHang::class, 'Ma_don_dat_hang', 'Ma_don_dat_hang');
    }

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'Ma_san_pham', 'Ma_san_pham');
    }
}
