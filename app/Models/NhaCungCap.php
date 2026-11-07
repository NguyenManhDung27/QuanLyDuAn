<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class NhaCungCap extends Model
{
    protected $table = 'Nha_cung_cap';

    protected $primaryKey = 'Ma_ncc';

    public function donDatHangs(): HasMany
    {
        return $this->hasMany(DonDatHang::class, 'Ma_ncc', 'Ma_ncc');
    }
}
