<?php

namespace Database\Seeders;

use App\Models\Kho;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 5 Vai trò chuẩn theo 01-tong-quan.md & 02-du-lieu.md
        $vaiTros = [
            ['Ma_vai_tro' => 'QUAN_TRI', 'Ten_vai_tro' => 'Quản trị'],
            ['Ma_vai_tro' => 'QL_KHO', 'Ten_vai_tro' => 'Quản lý kho'],
            ['Ma_vai_tro' => 'NV_KHO', 'Ten_vai_tro' => 'Nhân viên kho'],
            ['Ma_vai_tro' => 'KE_TOAN', 'Ten_vai_tro' => 'Kế toán'],
            ['Ma_vai_tro' => 'KINH_DOANH', 'Ten_vai_tro' => 'Kinh doanh'],
        ];

        foreach ($vaiTros as $vt) {
            VaiTro::updateOrCreate(['Ma_vai_tro' => $vt['Ma_vai_tro']], $vt);
        }

        // 2. Tạo 2 Kho hàng chuẩn (HN, HCM)
        $khos = [
            [
                'Ma_kho' => 'HN',
                'Ten_kho' => 'Kho Hà Nội',
                'Dia_chi' => '123 Phố Huế, Hai Bà Trưng, Hà Nội',
                'Trang_thai' => 'Hoạt động',
            ],
            [
                'Ma_kho' => 'HCM',
                'Ten_kho' => 'Kho TP.HCM',
                'Dia_chi' => '456 Nguyễn Thị Minh Khai, Quận 3, TP.HCM',
                'Trang_thai' => 'Hoạt động',
            ],
        ];

        foreach ($khos as $k) {
            Kho::updateOrCreate(['Ma_kho' => $k['Ma_kho']], $k);
        }

        // 3. Tạo tài khoản mẫu cho 5 Vai trò (Mật khẩu: 12345678)
        $nguoiDungs = [
            [
                'Ma_nguoi_dung' => 'ND0001',
                'Ten_dang_nhap' => 'quantri01',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Phạm Minh Khôi',
                'Email' => 'quantri@soulwine.vn',
                'So_dien_thoai' => '0901234561',
                'Ma_vai_tro' => 'QUAN_TRI',
                'Ma_kho' => null,
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            [
                'Ma_nguoi_dung' => 'ND0002',
                'Ten_dang_nhap' => 'qlkho.hn',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Nguyễn Đức Anh',
                'Email' => 'qlkho.hn@soulwine.vn',
                'So_dien_thoai' => '0901234562',
                'Ma_vai_tro' => 'QL_KHO',
                'Ma_kho' => 'HN',
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            [
                'Ma_nguoi_dung' => 'ND0003',
                'Ten_dang_nhap' => 'nvkho.hn',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Lê Văn Bình',
                'Email' => 'nvkho.hn@soulwine.vn',
                'So_dien_thoai' => '0901234563',
                'Ma_vai_tro' => 'NV_KHO',
                'Ma_kho' => 'HN',
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            [
                'Ma_nguoi_dung' => 'ND0004',
                'Ten_dang_nhap' => 'ketoan01',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Nguyễn Vân Anh',
                'Email' => 'ketoan@soulwine.vn',
                'So_dien_thoai' => '0901234564',
                'Ma_vai_tro' => 'KE_TOAN',
                'Ma_kho' => null,
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            [
                'Ma_nguoi_dung' => 'ND0005',
                'Ten_dang_nhap' => 'kinhdoanh01',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Lê Thị Huyền Trang',
                'Email' => 'kinhdoanh@soulwine.vn',
                'So_dien_thoai' => '0901234565',
                'Ma_vai_tro' => 'KINH_DOANH',
                'Ma_kho' => null,
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            // Tài khoản tiện test nhanh (alias)
            [
                'Ma_nguoi_dung' => 'ND0006',
                'Ten_dang_nhap' => 'admin',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Quản Trị Viên',
                'Email' => 'admin@soulwine.vn',
                'So_dien_thoai' => '0901234566',
                'Ma_vai_tro' => 'QUAN_TRI',
                'Ma_kho' => null,
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 0,
            ],
            // Tài khoản test đổi mật khẩu lần đầu
            [
                'Ma_nguoi_dung' => 'ND0007',
                'Ten_dang_nhap' => 'user_moi',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Nhân Viên Mới',
                'Email' => 'new@soulwine.vn',
                'So_dien_thoai' => '0901234567',
                'Ma_vai_tro' => 'NV_KHO',
                'Ma_kho' => 'HN',
                'Trang_thai' => 'Hoạt động',
                'Phai_doi_mat_khau' => true,
                'So_lan_sai_mat_khau' => 0,
            ],
            // Tài khoản bị khóa để test lọc và mở khóa
            [
                'Ma_nguoi_dung' => 'ND0008',
                'Ten_dang_nhap' => 'user_khoa',
                'Mat_khau' => Hash::make('12345678'),
                'Ho_ten' => 'Tài Khoản Đang Khóa',
                'Email' => 'khoa@soulwine.vn',
                'So_dien_thoai' => '0901234568',
                'Ma_vai_tro' => 'NV_KHO',
                'Ma_kho' => 'HN',
                'Trang_thai' => 'Khóa',
                'Phai_doi_mat_khau' => false,
                'So_lan_sai_mat_khau' => 5,
            ],
        ];

        foreach ($nguoiDungs as $nd) {
            NguoiDung::updateOrCreate(['Ma_nguoi_dung' => $nd['Ma_nguoi_dung']], $nd);
        }

        // 4. Danh mục Nhà cung cấp mẫu
        $nhacungcaps = [
            [
                'Ma_ncc' => 'NCC001',
                'Ten_ncc' => 'Vignobles Dupont',
                'Quoc_gia' => 'Pháp',
                'Dia_chi' => 'Route de Bordeaux, 33000 Bordeaux, France',
                'Ma_so_thue' => '0102938475',
                'Nguoi_lien_he' => 'Jean Dupont',
                'So_dien_thoai' => '+33 5 56 00 01 02',
                'Email' => 'contact@dupont-wine.fr',
                'Trang_thai' => 'Đang giao dịch',
            ],
            [
                'Ma_ncc' => 'NCC002',
                'Ten_ncc' => 'Castello Wine Group',
                'Quoc_gia' => 'Ý',
                'Dia_chi' => 'Via Chiantigiana 15, 50022 Greve in Chianti, Italy',
                'Ma_so_thue' => '0203948576',
                'Nguoi_lien_he' => 'Marco Rossi',
                'So_dien_thoai' => '+39 055 123456',
                'Email' => 'info@castellowine.it',
                'Trang_thai' => 'Đang giao dịch',
            ],
            [
                'Ma_ncc' => 'NCC003',
                'Ten_ncc' => 'Bodegas Iberia',
                'Quoc_gia' => 'Tây Ban Nha',
                'Dia_chi' => 'Calle Mayor 45, 26001 Logroño, Spain',
                'Ma_so_thue' => '0304958697',
                'Nguoi_lien_he' => 'Carlos Santana',
                'So_dien_thoai' => '+34 91 7654321',
                'Email' => 'carlos@bodegasiberia.es',
                'Trang_thai' => 'Ngừng giao dịch',
            ],
        ];

        foreach ($nhacungcaps as $ncc) {
            \App\Models\NhaCungCap::updateOrCreate(['Ma_ncc' => $ncc['Ma_ncc']], $ncc);
        }

        // 5. Danh mục Sản phẩm mẫu
        $sanphams = [
            [
                'Ma_san_pham' => 'SP0001',
                'Ten_san_pham' => 'Château Dupont Rouge',
                'Nien_vu' => 2018,
                'Loai_vang' => 'Đỏ',
                'Quoc_gia' => 'Pháp',
                'Vung' => 'Bordeaux',
                'Nha_san_xuat' => 'Vignobles Dupont',
                'Dung_tich' => 750,
                'Nong_do_con' => 13.5,
                'Don_vi_tinh' => 'Chai',
                'Quy_cach_thung' => 6,
                'Gia_niem_yet' => 1200000,
                'Gia_von_binh_quan' => 800000.00,
                'Trang_thai' => 'Đang kinh doanh',
            ],
            [
                'Ma_san_pham' => 'SP0002',
                'Ten_san_pham' => 'Chablis Blanc Premier Cru',
                'Nien_vu' => null,
                'Loai_vang' => 'Trắng',
                'Quoc_gia' => 'Pháp',
                'Vung' => 'Bourgogne',
                'Nha_san_xuat' => 'Domaine Chablis',
                'Dung_tich' => 750,
                'Nong_do_con' => 12.5,
                'Don_vi_tinh' => 'Chai',
                'Quy_cach_thung' => 6,
                'Gia_niem_yet' => 650000,
                'Gia_von_binh_quan' => 0.00,
                'Trang_thai' => 'Đang kinh doanh',
            ],
            [
                'Ma_san_pham' => 'SP0003',
                'Ten_san_pham' => 'Chianti Classico Riserva',
                'Nien_vu' => 2019,
                'Loai_vang' => 'Đỏ',
                'Quoc_gia' => 'Ý',
                'Vung' => 'Tuscany',
                'Nha_san_xuat' => 'Castello di Brolio',
                'Dung_tich' => 750,
                'Nong_do_con' => 14.0,
                'Don_vi_tinh' => 'Chai',
                'Quy_cach_thung' => 6,
                'Gia_niem_yet' => 980000,
                'Gia_von_binh_quan' => 620000.00,
                'Trang_thai' => 'Đang kinh doanh',
            ],
            [
                'Ma_san_pham' => 'SP0004',
                'Ten_san_pham' => 'Prosecco Superiore DOCG',
                'Nien_vu' => null,
                'Loai_vang' => 'Vang nổ',
                'Quoc_gia' => 'Ý',
                'Vung' => 'Veneto',
                'Nha_san_xuat' => 'Villa Sandi',
                'Dung_tich' => 750,
                'Nong_do_con' => 11.0,
                'Don_vi_tinh' => 'Chai',
                'Quy_cach_thung' => 6,
                'Gia_niem_yet' => null, // Chưa có giá niêm yết
                'Gia_von_binh_quan' => 0.00,
                'Trang_thai' => 'Đang kinh doanh',
            ],
            [
                'Ma_san_pham' => 'SP0005',
                'Ten_san_pham' => 'Rioja Gran Reserva 2015',
                'Nien_vu' => 2015,
                'Loai_vang' => 'Đỏ',
                'Quoc_gia' => 'Tây Ban Nha',
                'Vung' => 'Rioja',
                'Nha_san_xuat' => 'Bodegas Faustino',
                'Dung_tich' => 750,
                'Nong_do_con' => 13.5,
                'Don_vi_tinh' => 'Chai',
                'Quy_cach_thung' => 12,
                'Gia_niem_yet' => 1450000,
                'Gia_von_binh_quan' => 950000.00,
                'Trang_thai' => 'Ngừng',
            ],
        ];

        foreach ($sanphams as $sp) {
            \App\Models\SanPham::updateOrCreate(['Ma_san_pham' => $sp['Ma_san_pham']], $sp);
        }

        // 6. Danh mục Khách hàng mẫu
        $khachhangs = [
            [
                'Ma_khach_hang' => 'KH0001',
                'Ten_khach_hang' => 'Nhà hàng Sen Tây Hồ',
                'Loai_khach_hang' => 'Doanh nghiệp',
                'Ma_so_thue' => '0105678901',
                'So_dien_thoai' => '02437199242',
                'Email' => 'ketoan@sentayho.com.vn',
                'Dia_chi' => '614 Lạc Long Quân, Tây Hồ, Hà Nội',
                'Trang_thai' => 'Đang giao dịch',
            ],
            [
                'Ma_khach_hang' => 'KH0002',
                'Ten_khach_hang' => 'Khách sạn Caravelle Sài Gòn',
                'Loai_khach_hang' => 'Doanh nghiệp',
                'Ma_so_thue' => '0301234567',
                'So_dien_thoai' => '02838234999',
                'Email' => 'fnb@caravellehotel.com',
                'Dia_chi' => '19-23 Lam Sơn, Bến Nghé, Quận 1, TP.HCM',
                'Trang_thai' => 'Đang giao dịch',
            ],
            [
                'Ma_khach_hang' => 'KH0003',
                'Ten_khach_hang' => 'Nguyễn Hoàng Long',
                'Loai_khach_hang' => 'Cá nhân',
                'Ma_so_thue' => null,
                'So_dien_thoai' => '0912345678',
                'Email' => 'longnh@gmail.com',
                'Dia_chi' => '45 Lê Duẩn, Hoàn Kiếm, Hà Nội',
                'Trang_thai' => 'Đang giao dịch',
            ],
            [
                'Ma_khach_hang' => 'KH0004',
                'Ten_khach_hang' => 'Công ty TNHH Ẩm Thực Á Âu',
                'Loai_khach_hang' => 'Doanh nghiệp',
                'Ma_so_thue' => '0109988776',
                'So_dien_thoai' => '0987654321',
                'Email' => 'info@aaucuisine.vn',
                'Dia_chi' => '88 Hai Bà Trưng, Quận 1, TP.HCM',
                'Trang_thai' => 'Ngừng giao dịch',
            ],
        ];

        foreach ($khachhangs as $kh) {
            \App\Models\KhachHang::updateOrCreate(['Ma_khach_hang' => $kh['Ma_khach_hang']], $kh);
        }

        // 7. Lô mẫu (Lo)
        $los = [
            [
                'Ma_lo' => 'L2201',
                'Ma_san_pham' => 'SP0001',
                'Ma_lo_ncc' => 'LOT-FR-2201',
                'Ngay_san_xuat' => '2022-03-15',
                'Han_su_dung' => '2032-03-15',
            ],
            [
                'Ma_lo' => 'L2303',
                'Ma_san_pham' => 'SP0003',
                'Ma_lo_ncc' => 'LOT-IT-2303',
                'Ngay_san_xuat' => '2023-05-10',
                'Han_su_dung' => '2033-05-10',
            ],
            [
                'Ma_lo' => 'L2304',
                'Ma_san_pham' => 'SP0003',
                'Ma_lo_ncc' => 'LOT-IT-2304',
                'Ngay_san_xuat' => '2023-06-12',
                'Han_su_dung' => '2033-06-12',
            ],
        ];

        foreach ($los as $lo) {
            \App\Models\Lo::updateOrCreate(['Ma_lo' => $lo['Ma_lo']], $lo);
        }

        // 8. Tồn kho đầu kỳ (Ton_kho) theo 08-kich-ban-dau-cuoi.md §0
        $tonkhos = [
            [
                'Ma_kho' => 'HN',
                'Ma_lo' => 'L2201',
                'Ton_thuc' => 10,
                'Ngay_nhap_dau_tien' => '2026-01-15 08:30:00',
            ],
            [
                'Ma_kho' => 'HN',
                'Ma_lo' => 'L2303',
                'Ton_thuc' => 15,
                'Ngay_nhap_dau_tien' => '2026-02-01 09:00:00',
            ],
            [
                'Ma_kho' => 'HN',
                'Ma_lo' => 'L2304',
                'Ton_thuc' => 25,
                'Ngay_nhap_dau_tien' => '2026-02-20 10:00:00',
            ],
            [
                'Ma_kho' => 'HCM',
                'Ma_lo' => 'L2303',
                'Ton_thuc' => 20,
                'Ngay_nhap_dau_tien' => '2026-02-05 14:00:00',
            ],
        ];

        foreach ($tonkhos as $tk) {
            \App\Models\TonKho::updateOrCreate(
                ['Ma_kho' => $tk['Ma_kho'], 'Ma_lo' => $tk['Ma_lo']],
                $tk
            );
        }
    }
}
