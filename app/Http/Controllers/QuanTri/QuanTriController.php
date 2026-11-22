<?php

namespace App\Http\Controllers\QuanTri;

use App\Http\Controllers\Controller;
use App\Models\Kho;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class QuanTriController extends Controller
{
    /**
     * ADM-01: Trang chủ Quản trị (GET /quan-tri)
     */
    public function trangChu()
    {
        $soTaiKhoanKhoa = NguoiDung::where('Trang_thai', 'Khóa')->count();
        $tongNguoiDung = NguoiDung::count();
        $tongKho = Kho::count();

        // 10 nhật ký gần đây nhất
        $nhatKyGanDay = Activity::latest()->take(10)->get();

        return view('quantri.trang-chu', compact(
            'soTaiKhoanKhoa',
            'tongNguoiDung',
            'tongKho',
            'nhatKyGanDay'
        ));
    }
}

