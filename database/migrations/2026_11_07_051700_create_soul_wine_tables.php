<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Theo mục 3.1.3 tài liệu thiết kế CSDL. Các cột "bắt buộc khi..." để NULL, tầng ứng dụng kiểm tra.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Vai_tro', function (Blueprint $t) {
            $t->string('Ma_vai_tro', 20)->primary();
            $t->string('Ten_vai_tro', 100)->unique();
        });

        Schema::create('Kho', function (Blueprint $t) {
            $t->string('Ma_kho', 10)->primary();
            $t->string('Ten_kho', 100)->unique();
            $t->string('Dia_chi', 255);
            $t->string('Trang_thai', 20)->default('Hoạt động');
        });

        Schema::create('Nguoi_dung', function (Blueprint $t) {
            $t->string('Ma_nguoi_dung', 20)->primary();
            $t->string('Ten_dang_nhap', 50)->unique();
            $t->string('Mat_khau', 255);
            $t->string('Ho_ten', 100);
            $t->string('Email', 100)->unique();
            $t->string('So_dien_thoai', 20)->nullable();
            $this->fk($t, 'Ma_vai_tro', 20, 'Vai_tro');
            $this->fk($t, 'Ma_kho', 10, 'Kho', nullable: true);
            $t->string('Trang_thai', 20)->default('Hoạt động');
            $t->boolean('Phai_doi_mat_khau')->default(true);
            $t->unsignedTinyInteger('So_lan_sai_mat_khau')->default(0);
        });

        Schema::create('San_pham', function (Blueprint $t) {
            $t->string('Ma_san_pham', 20)->primary();
            $t->string('Ten_san_pham', 255)->index();
            $t->unsignedSmallInteger('Nien_vu')->nullable();
            $t->string('Loai_vang', 20)->index();
            $t->string('Quoc_gia', 100);
            $t->string('Vung', 100)->nullable();
            $t->string('Nha_san_xuat', 255);
            $t->unsignedInteger('Dung_tich');
            $t->decimal('Nong_do_con', 4, 1)->nullable();
            $t->string('Don_vi_tinh', 20)->default('Chai');
            $t->unsignedSmallInteger('Quy_cach_thung')->nullable();
            $t->decimal('Gia_niem_yet', 18, 0)->nullable();
            $t->decimal('Gia_von_binh_quan', 18, 2)->default(0);
            $t->string('Trang_thai', 20)->default('Đang kinh doanh');
        });

        Schema::create('Lo', function (Blueprint $t) {
            $t->string('Ma_lo', 20)->primary();
            $this->fk($t, 'Ma_san_pham', 20, 'San_pham');
            $t->string('Ma_lo_ncc', 50);
            $t->date('Ngay_san_xuat')->nullable();
            $t->date('Han_su_dung')->nullable();
            $t->unique(['Ma_san_pham', 'Ma_lo_ncc']);
        });

        Schema::create('Nha_cung_cap', function (Blueprint $t) {
            $t->string('Ma_ncc', 20)->primary();
            $t->string('Ten_ncc', 255)->index();
            $t->string('Quoc_gia', 100);
            $t->string('Dia_chi', 255)->nullable();
            $t->string('Ma_so_thue', 20)->nullable();
            $t->string('Nguoi_lien_he', 100)->nullable();
            $t->string('So_dien_thoai', 20)->nullable();
            $t->string('Email', 100)->nullable();
            $t->string('Trang_thai', 20)->default('Đang giao dịch');
        });

        Schema::create('Khach_hang', function (Blueprint $t) {
            $t->string('Ma_khach_hang', 20)->primary();
            $t->string('Ten_khach_hang', 255)->index();
            $t->string('Loai_khach_hang', 20);
            $t->string('Ma_so_thue', 20)->nullable();
            $t->string('So_dien_thoai', 20)->index();
            $t->string('Email', 100)->nullable();
            $t->string('Dia_chi', 255)->nullable();
            $t->string('Trang_thai', 20)->default('Đang giao dịch');
        });

        Schema::create('Don_dat_hang', function (Blueprint $t) {
            $t->string('Ma_don_dat_hang', 20)->primary();
            $this->fk($t, 'Ma_ncc', 20, 'Nha_cung_cap');
            $this->fk($t, 'Ma_kho', 10, 'Kho');
            $this->nguoiDung($t, 'Ma_nguoi_lap', nullable: false);
            $t->date('Ngay_lap');
            $t->date('Ngay_du_kien_nhan_du')->nullable();
            $t->string('Trang_thai', 30)->default('Đang cập nhật')->index();
            $t->decimal('Tong_gia_tri', 18, 0)->default(0);
            $this->nguoiDung($t, 'Ma_nguoi_duyet');
            $t->dateTime('Thoi_diem_duyet')->nullable();
            $t->string('Ghi_chu_phe_duyet', 1000)->nullable();
            $t->string('Ly_do_tu_choi', 1000)->nullable();
            $this->nguoiDung($t, 'Ma_nguoi_gui_ncc');
            $t->date('Ngay_gui_ncc')->nullable();
            $this->nguoiDung($t, 'Ma_nguoi_huy');
            $t->dateTime('Thoi_diem_huy')->nullable();
            $t->string('Ly_do_huy', 1000)->nullable();
            $this->nguoiDung($t, 'Ma_nguoi_dong_thieu');
            $t->dateTime('Thoi_diem_dong_thieu')->nullable();
            $t->string('Ly_do_dong_thieu', 1000)->nullable();
        });

        Schema::create('Don_hang_ban', function (Blueprint $t) {
            $t->string('Ma_don_hang_ban', 20)->primary();
            $this->fk($t, 'Ma_khach_hang', 20, 'Khach_hang');
            $this->fk($t, 'Ma_kho', 10, 'Kho');
            $this->nguoiDung($t, 'Ma_nguoi_lap', nullable: false);
            $t->dateTime('Thoi_diem_xac_nhan')->index();
            $t->string('Ten_nguoi_nhan', 100);
            $t->string('Sdt_nguoi_nhan', 20);
            $t->string('Dia_chi_giao_hang', 255);
            $t->decimal('Tong_tien_hang', 18, 0);
            $t->string('Trang_thai', 30)->default('Đang chuẩn bị hàng')->index();
            $t->dateTime('Thoi_diem_huy')->nullable();
            $t->string('Ly_do_huy', 1000)->nullable();
        });

        Schema::create('Phieu_xuat_kho', function (Blueprint $t) {
            $t->string('Ma_phieu_xuat', 20)->primary();
            $t->string('Ma_don_hang_ban', 20)->unique();
            $t->foreign('Ma_don_hang_ban')->references('Ma_don_hang_ban')->on('Don_hang_ban');
            $t->date('Ngay_tao');
            $t->string('Trang_thai_giao_hang', 30)->default('Đang chuẩn bị hàng')->index();
            $t->string('Trang_thai_hoan_hang', 30)->default('Chưa xử lý')->index();
            $t->string('Don_vi_van_chuyen', 100)->nullable();
            $t->string('Ma_van_don', 50)->nullable()->index();
            $this->nguoiDung($t, 'Ma_nguoi_xuat');
            $t->dateTime('Thoi_diem_xuat_kho')->nullable();
            $t->dateTime('Thoi_diem_ket_qua_giao')->nullable()->index();
            $t->string('Ly_do_giao_that_bai', 1000)->nullable();
            $t->dateTime('Thoi_diem_xu_ly_hoan')->nullable();
            $t->string('Ly_do_khong_nhap_kho', 1000)->nullable();
        });

        Schema::create('Phieu_nhap_kho', function (Blueprint $t) {
            $t->string('Ma_phieu_nhap', 20)->primary();
            $this->fk($t, 'Ma_kho', 10, 'Kho');
            $t->string('Loai_nhap', 30)->index();
            $this->fk($t, 'Ma_don_dat_hang', 20, 'Don_dat_hang', nullable: true);
            $this->fk($t, 'Ma_phieu_xuat', 20, 'Phieu_xuat_kho', nullable: true);
            $this->nguoiDung($t, 'Ma_nguoi_lap', nullable: false);
            $t->date('Ngay_lap');
            $t->dateTime('Thoi_diem_xac_nhan')->nullable();
            $t->string('So_phieu_giao_ncc', 50)->nullable();
            $t->boolean('Dot_giao_cuoi')->default(false);
            $t->string('Ly_do_tra_hang', 1000)->nullable();
            $t->string('Trang_thai', 30)->default('Đang cập nhật')->index();
        });

        Schema::create('Phieu_kiem_ke', function (Blueprint $t) {
            $t->string('Ma_phieu_kiem_ke', 20)->primary();
            $this->fk($t, 'Ma_kho', 10, 'Kho');
            $this->nguoiDung($t, 'Ma_nguoi_tao', nullable: false);
            $this->nguoiDung($t, 'Ma_nguoi_kiem');
            $t->dateTime('Thoi_diem_tao');
            $t->string('Trang_thai', 30)->default('Đang kiểm')->index();
            $t->dateTime('Thoi_diem_dem_xong')->nullable();
            $this->nguoiDung($t, 'Ma_nguoi_duyet');
            $t->dateTime('Thoi_diem_duyet')->nullable();
            $t->string('Ly_do_khong_duyet', 1000)->nullable();
            $t->dateTime('Thoi_diem_huy')->nullable();
            $t->string('Ly_do_huy', 1000)->nullable();
            $t->string('Ghi_chu', 1000)->nullable();
        });

        Schema::create('Ton_kho', function (Blueprint $t) {
            $this->fk($t, 'Ma_kho', 10, 'Kho');
            $this->fk($t, 'Ma_lo', 20, 'Lo');
            $t->unsignedInteger('Ton_thuc')->default(0);
            $t->dateTime('Ngay_nhap_dau_tien')->index();
            $t->primary(['Ma_kho', 'Ma_lo']);
        });

        Schema::create('Chi_tiet_don_dat_hang', function (Blueprint $t) {
            $this->fk($t, 'Ma_don_dat_hang', 20, 'Don_dat_hang');
            $this->fk($t, 'Ma_san_pham', 20, 'San_pham');
            $t->unsignedInteger('So_luong_dat');
            $t->decimal('Don_gia_mua', 18, 0);
            $t->primary(['Ma_don_dat_hang', 'Ma_san_pham']);
        });

        Schema::create('Chi_tiet_phieu_nhap', function (Blueprint $t) {
            $this->fk($t, 'Ma_phieu_nhap', 20, 'Phieu_nhap_kho');
            $this->fk($t, 'Ma_lo', 20, 'Lo');
            $t->unsignedInteger('So_luong_nhap');
            $t->unsignedInteger('So_luong_hao_hut')->default(0);
            $t->string('Ly_do_hao_hut', 1000)->nullable();
            $t->primary(['Ma_phieu_nhap', 'Ma_lo']);
        });

        Schema::create('Chi_tiet_don_ban', function (Blueprint $t) {
            $this->fk($t, 'Ma_don_hang_ban', 20, 'Don_hang_ban');
            $this->fk($t, 'Ma_san_pham', 20, 'San_pham');
            $t->unsignedInteger('So_luong');
            $t->decimal('Don_gia_ban', 18, 0);
            $t->decimal('Chiet_khau', 5, 2)->default(0);
            $t->primary(['Ma_don_hang_ban', 'Ma_san_pham']);
        });

        Schema::create('Chi_tiet_phieu_xuat', function (Blueprint $t) {
            $this->fk($t, 'Ma_phieu_xuat', 20, 'Phieu_xuat_kho');
            $t->string('Ma_lo_phan_bo', 20);
            $t->foreign('Ma_lo_phan_bo')->references('Ma_lo')->on('Lo');
            $t->string('Ma_lo_thuc_xuat', 20);
            $t->foreign('Ma_lo_thuc_xuat')->references('Ma_lo')->on('Lo');
            $t->unsignedInteger('So_luong');
            $t->string('Ly_do_doi_lo', 30)->nullable();
            $t->unsignedInteger('So_luong_hong')->nullable();
            $t->decimal('Gia_von_hang_hong', 18, 2)->nullable();
            $this->nguoiDung($t, 'Ma_nguoi_doi_lo');
            $t->dateTime('Thoi_diem_doi_lo')->nullable();
            $t->decimal('Gia_von_xuat', 18, 2)->nullable();
            $t->primary(['Ma_phieu_xuat', 'Ma_lo_phan_bo', 'Ma_lo_thuc_xuat']);
        });

        Schema::create('Chi_tiet_kiem_ke', function (Blueprint $t) {
            $this->fk($t, 'Ma_phieu_kiem_ke', 20, 'Phieu_kiem_ke');
            $this->fk($t, 'Ma_lo', 20, 'Lo');
            $t->unsignedInteger('So_luong_he_thong');
            $t->unsignedInteger('So_luong_thuc_te')->nullable();
            $t->string('Ly_do_chenh_lech', 1000)->nullable();
            $t->decimal('Gia_von_dieu_chinh', 18, 2)->nullable();
            $t->primary(['Ma_phieu_kiem_ke', 'Ma_lo']);
        });

        // SQLite (dùng khi chạy test) không hỗ trợ ALTER TABLE ADD CHECK.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE San_pham ADD CONSTRAINT chk_dung_tich CHECK (Dung_tich > 0)');
            DB::statement('ALTER TABLE Chi_tiet_don_dat_hang ADD CONSTRAINT chk_so_luong_dat CHECK (So_luong_dat > 0)');
            DB::statement('ALTER TABLE Chi_tiet_don_ban ADD CONSTRAINT chk_so_luong_ban CHECK (So_luong > 0)');
            DB::statement('ALTER TABLE Chi_tiet_don_ban ADD CONSTRAINT chk_chiet_khau CHECK (Chiet_khau BETWEEN 0 AND 100)');
            DB::statement('ALTER TABLE Chi_tiet_phieu_xuat ADD CONSTRAINT chk_so_luong_xuat CHECK (So_luong > 0)');
        }
    }

    public function down(): void
    {
        foreach ([
            'Chi_tiet_kiem_ke', 'Chi_tiet_phieu_xuat', 'Chi_tiet_don_ban', 'Chi_tiet_phieu_nhap',
            'Chi_tiet_don_dat_hang', 'Ton_kho', 'Phieu_kiem_ke', 'Phieu_nhap_kho', 'Phieu_xuat_kho',
            'Don_hang_ban', 'Don_dat_hang', 'Khach_hang', 'Nha_cung_cap', 'Lo', 'San_pham',
            'Nguoi_dung', 'Kho', 'Vai_tro',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    // Cột khóa ngoại trùng tên với khóa chính của bảng được tham chiếu.
    private function fk(Blueprint $t, string $column, int $length, string $on, bool $nullable = false): void
    {
        $t->string($column, $length)->nullable($nullable);
        $t->foreign($column)->references($column)->on($on);
    }

    private function nguoiDung(Blueprint $t, string $column, bool $nullable = true): void
    {
        $t->string($column, 20)->nullable($nullable);
        $t->foreign($column)->references('Ma_nguoi_dung')->on('Nguoi_dung');
    }
};
