<?php

namespace App\Http\Controllers\KinhDoanh;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;

class SanPhamGiaController extends Controller
{
    public function index(Request $request)
    {
        $query = SanPham::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_san_pham', 'like', "%{$search}%")
                    ->orWhere('Ten_san_pham', 'like', "%{$search}%")
                    ->orWhere('Nha_san_xuat', 'like', "%{$search}%");
            });
        }

        if ($loaiVang = $request->input('loai_vang')) {
            if ($loaiVang !== 'Tất cả') {
                $query->where('Loai_vang', $loaiVang);
            }
        }

        if ($quocGia = $request->input('quoc_gia')) {
            if ($quocGia !== 'Tất cả') {
                $query->where('Quoc_gia', $quocGia);
            }
        }

        if ($request->boolean('chua_co_gia')) {
            $query->whereNull('Gia_niem_yet');
        }

        $sanPhams = $query->orderBy('Ma_san_pham', 'asc')->paginate(15)->withQueryString();

        $dsQuocGia = SanPham::select('Quoc_gia')->distinct()->pluck('Quoc_gia');

        // Tính tồn khả dụng theo kho HN và HCM cho các sản phẩm trong trang hiện tại
        $maSanPhams = $sanPhams->pluck('Ma_san_pham')->toArray();
        $tonKhaDungMap = StockService::getTonKhaDungMap($maSanPhams);

        $editingMa = $request->input('edit');

        return view('kinhdoanh.san-pham.index', compact('sanPhams', 'dsQuocGia', 'tonKhaDungMap', 'editingMa'));
    }

    public function updatePrice(Request $request, string $ma)
    {
        $sp = SanPham::where('Ma_san_pham', $ma)->firstOrFail();

        $validated = $request->validate([
            'Gia_niem_yet' => 'required|numeric|min:0',
        ], [
            'Gia_niem_yet.required' => 'Vui lòng nhập giá niêm yết',
            'Gia_niem_yet.numeric' => 'Giá niêm yết phải là số',
            'Gia_niem_yet.min' => 'Giá niêm yết không được âm',
        ]);

        $oldPrice = $sp->Gia_niem_yet;
        $newPrice = (float) $validated['Gia_niem_yet'];

        $sp->update(['Gia_niem_yet' => $newPrice]);

        $oldFormatted = $oldPrice !== null ? number_format($oldPrice, 0, ',', '.') . ' đ' : 'Chưa có giá';
        $newFormatted = number_format($newPrice, 0, ',', '.') . ' đ';

        ActivityLogger::log(
            'Sửa',
            'Danh mục',
            "Cập nhật giá niêm yết sản phẩm {$sp->Ten_san_pham} ({$ma}): {$oldFormatted} -> {$newFormatted}",
            $sp,
            [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ]
        );

        return redirect()->route('kinh-doanh.san-pham.index')
            ->with('success', "Đã cập nhật giá niêm yết sản phẩm {$ma} thành {$newFormatted}.");
    }
}

