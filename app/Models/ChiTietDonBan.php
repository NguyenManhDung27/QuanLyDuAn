<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDonBan extends ChiTietModel
{
    protected $table = 'Chi_tiet_don_ban';

    protected $primaryKey = 'Ma_don_hang_ban';

    protected array $khoaGhep = ['Ma_don_hang_ban', 'Ma_san_pham'];

    public function donHangBan(): BelongsTo
    {
        return $this->belongsTo(DonHangBan::class, 'Ma_don_hang_ban', 'Ma_don_hang_ban');
    }

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'Ma_san_pham', 'Ma_san_pham');
    }
}
