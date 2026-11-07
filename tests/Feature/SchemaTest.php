<?php

namespace Tests\Feature;

use App\Models\ChiTietDonDatHang;
use App\Models\DonDatHang;
use App\Models\Kho;
use App\Models\NguoiDung;
use App\Models\NhaCungCap;
use App\Models\SanPham;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    private function nguoiDung(): NguoiDung
    {
        VaiTro::create(['Ma_vai_tro' => 'KE_TOAN', 'Ten_vai_tro' => 'Kế toán']);

        return NguoiDung::create([
            'Ma_nguoi_dung' => 'ND000001', 'Ten_dang_nhap' => 'a', 'Mat_khau' => 'secret',
            'Ho_ten' => 'A', 'Email' => 'a@example.com', 'Ma_vai_tro' => 'KE_TOAN',
        ]);
    }

    public function test_tao_du_18_bang_nghiep_vu(): void
    {
        foreach ([
            'Vai_tro', 'Kho', 'Nguoi_dung', 'San_pham', 'Lo', 'Nha_cung_cap', 'Khach_hang', 'Don_dat_hang',
            'Phieu_nhap_kho', 'Don_hang_ban', 'Phieu_xuat_kho', 'Phieu_kiem_ke', 'Ton_kho',
            'Chi_tiet_don_dat_hang', 'Chi_tiet_phieu_nhap', 'Chi_tiet_don_ban', 'Chi_tiet_phieu_xuat', 'Chi_tiet_kiem_ke',
        ] as $bang) {
            $this->assertTrue(Schema::hasTable($bang), $bang);
        }
    }

    public function test_dang_nhap_va_dat_lai_mat_khau_dung_nguoi_dung(): void
    {
        $this->nguoiDung();

        $this->assertTrue(Auth::attempt(['Ten_dang_nhap' => 'a', 'password' => 'secret']));
        $this->assertSame('ND000001', Auth::id());
        Notification::fake(); // chưa có route password.reset
        $this->assertSame(Password::RESET_LINK_SENT, Password::sendResetLink(['Email' => 'a@example.com']));
    }

    public function test_khoa_ghep_chi_sua_va_xoa_dung_mot_dong(): void
    {
        $this->nguoiDung();
        Kho::create(['Ma_kho' => 'HN', 'Ten_kho' => 'Hà Nội', 'Dia_chi' => 'x']);
        NhaCungCap::create(['Ma_ncc' => 'NCC1', 'Ten_ncc' => 'n', 'Quoc_gia' => 'Pháp']);
        foreach (['SP1', 'SP2'] as $ma) {
            SanPham::create(['Ma_san_pham' => $ma, 'Ten_san_pham' => $ma, 'Loai_vang' => 'Đỏ', 'Quoc_gia' => 'Pháp', 'Nha_san_xuat' => 'x', 'Dung_tich' => 750]);
        }
        $don = DonDatHang::create(['Ma_don_dat_hang' => 'DDH000001', 'Ma_ncc' => 'NCC1', 'Ma_kho' => 'HN', 'Ma_nguoi_lap' => 'ND000001', 'Ngay_lap' => '2026-11-07']);
        $dong1 = $don->chiTiets()->create(['Ma_san_pham' => 'SP1', 'So_luong_dat' => 1, 'Don_gia_mua' => 100]);
        $don->chiTiets()->create(['Ma_san_pham' => 'SP2', 'So_luong_dat' => 2, 'Don_gia_mua' => 200]);

        $dong1->update(['So_luong_dat' => 9]);
        $this->assertSame(2, ChiTietDonDatHang::where('Ma_san_pham', 'SP2')->value('So_luong_dat'));
        $this->assertSame(9, ChiTietDonDatHang::where('Ma_san_pham', 'SP1')->value('So_luong_dat'));

        $dong1->delete();
        $this->assertSame(1, $don->chiTiets()->count());
        $this->assertSame('NCC1', $don->nhaCungCap->Ma_ncc);
    }
}
