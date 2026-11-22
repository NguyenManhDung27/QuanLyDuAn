<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\Kho;
use App\Models\SanPham;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TonKhoController extends Controller
{
    /**
     * Màn hình QLK-02: Theo dõi tồn kho
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';
        $kho = Kho::find($maKho);
        $tenKho = $kho ? $kho->Ten_kho : ('Kho ' . $maKho);

        $query = SanPham::query();

        // Lọc theo từ khóa tìm kiếm (Mã SP, Tên SP, Nhà sản xuất)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_san_pham', 'like', "%{$search}%")
                  ->orWhere('Ten_san_pham', 'like', "%{$search}%")
                  ->orWhere('Nha_san_xuat', 'like', "%{$search}%");
            });
        }

        // Lọc theo Loại vang
        if ($loaiVang = $request->input('loai_vang')) {
            if ($loaiVang !== 'Tất cả') {
                $query->where('Loai_vang', $loaiVang);
            }
        }

        // Lọc theo Quốc gia
        if ($quocGia = $request->input('quoc_gia')) {
            if ($quocGia !== 'Tất cả') {
                $query->where('Quoc_gia', $quocGia);
            }
        }

        // Lọc theo Niên vụ
        if ($nienVu = $request->input('nien_vu')) {
            $query->where('Nien_vu', $nienVu);
        }

        $sanPhams = $query->orderBy('Ma_san_pham')->get();
        $maSanPhams = $sanPhams->pluck('Ma_san_pham')->all();

        // Tính toán tồn kho theo batch
        $tonThucMap = StockService::getTonThucMap($maSanPhams);
        $tonKhaDungMap = StockService::getTonKhaDungMap($maSanPhams);
        $tonDuKienMap = StockService::getTonDuKienMap($maKho, $maSanPhams);
        $lotsMap = StockService::getChiTietLoMap($maKho, $maSanPhams);

        // Danh sách cho bộ lọc dropdown
        $loaiVangList = SanPham::whereNotNull('Loai_vang')
            ->distinct()
            ->orderBy('Loai_vang')
            ->pluck('Loai_vang');

        $quocGiaList = SanPham::whereNotNull('Quoc_gia')
            ->distinct()
            ->orderBy('Quoc_gia')
            ->pluck('Quoc_gia');

        return view('kho.ton-kho.index', [
            'sanPhams' => $sanPhams,
            'maKho' => $maKho,
            'tenKho' => $tenKho,
            'tonThucMap' => $tonThucMap,
            'tonKhaDungMap' => $tonKhaDungMap,
            'tonDuKienMap' => $tonDuKienMap,
            'lotsMap' => $lotsMap,
            'loaiVangList' => $loaiVangList,
            'quocGiaList' => $quocGiaList,
            'filters' => [
                'search' => $search,
                'loai_vang' => $request->input('loai_vang', 'Tất cả'),
                'quoc_gia' => $request->input('quoc_gia', 'Tất cả'),
                'nien_vu' => $request->input('nien_vu', ''),
            ],
        ]);
    }
}

