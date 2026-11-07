<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use Notifiable;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'Nguoi_dung';

    protected $primaryKey = 'Ma_nguoi_dung';

    protected $keyType = 'string';

    // Bảng không có cột remember_token theo thiết kế; để rỗng nên Laravel bỏ qua "ghi nhớ đăng nhập".
    protected $rememberTokenName = '';

    // Trang_thai, Phai_doi_mat_khau, So_lan_sai_mat_khau do hệ thống cập nhật, không nhận từ form.
    protected $fillable = [
        'Ma_nguoi_dung', 'Ten_dang_nhap', 'Mat_khau', 'Ho_ten', 'Email', 'So_dien_thoai', 'Ma_vai_tro', 'Ma_kho',
    ];

    protected $hidden = ['Mat_khau'];

    protected $casts = [
        'Mat_khau' => 'hashed',
        'Phai_doi_mat_khau' => 'boolean',
    ];

    public function getAuthPasswordName(): string
    {
        return 'Mat_khau';
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->Email;
    }

    public function routeNotificationForMail(): string
    {
        return $this->Email;
    }

    public function vaiTro(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'Ma_vai_tro', 'Ma_vai_tro');
    }

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }
}
