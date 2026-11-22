<?php

namespace App\Http\Controllers\QuanTri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class NhatKyController extends Controller
{
    /**
     * ADM-06: Nhật ký hệ thống (GET /quan-tri/nhat-ky)
     */
    public function index(Request $request)
    {
        $query = Activity::query()->with('causer');

        // Lọc từ ngày
        if ($tuNgay = $request->input('tu_ngay')) {
            $query->whereDate('created_at', '>=', $tuNgay);
        }

        // Lọc đến ngày
        if ($denNgay = $request->input('den_ngay')) {
            $query->whereDate('created_at', '<=', $denNgay);
        }

        // Lọc theo người thực hiện
        if ($search = $request->input('nguoi_thuc_hien')) {
            $query->where(function ($q) use ($search) {
                $q->whereHasMorph('causer', [\App\Models\NguoiDung::class], function ($sub) use ($search) {
                    $sub->where('Ho_ten', 'LIKE', "%{$search}%")
                        ->orWhere('Ten_dang_nhap', 'LIKE', "%{$search}%");
                })->orWhere('properties->user_name', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo loại chứng từ (log_name)
        if ($loaiChungTu = $request->input('loai_chung_tu')) {
            $query->where('log_name', $loaiChungTu);
        }

        // Lọc theo thao tác (event)
        if ($thaoTac = $request->input('thao_tac')) {
            $query->where('event', $thaoTac);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('quantri.nhat-ky.index', compact('logs'));
    }
}

