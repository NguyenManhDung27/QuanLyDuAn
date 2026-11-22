<?php

namespace App\Http\Controllers\KeToan;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use App\Models\NhaCungCap;
use App\Models\SanPham;
use App\Services\StockService;
use Illuminate\Http\Request;

class DanhMucController extends Controller
{
    /**
     * KT-05: Sản phẩm (chỉ xem, có giá vốn và giá trị tồn)
     */
    public function sanPham(Request $request)
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

        $sanPhams = $query->orderBy('Ma_san_pham', 'asc')->paginate(15)->withQueryString();

        $maSanPhams = $sanPhams->pluck('Ma_san_pham')->toArray();
        $tonThucMap = StockService::getTonThucMap($maSanPhams);

        return view('ketoan.san-pham.index', compact('sanPhams', 'tonThucMap'));
    }

    /**
     * KT-06: Nhà cung cấp (chỉ xem)
     */
    public function nhaCungCap(Request $request)
    {
        $query = NhaCungCap::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_ncc', 'like', "%{$search}%")
                    ->orWhere('Ten_ncc', 'like', "%{$search}%")
                    ->orWhere('Ma_so_thue', 'like', "%{$search}%");
            });
        }

        if ($quocGia = $request->input('quoc_gia')) {
            if ($quocGia !== 'Tất cả') {
                $query->where('Quoc_gia', $quocGia);
            }
        }

        if ($trangThai = $request->input('trang_thai')) {
            if ($trangThai !== 'Tất cả') {
                $query->where('Trang_thai', $trangThai);
            }
        }

        $nhaCungCaps = $query->orderBy('Ma_ncc', 'asc')->paginate(15)->withQueryString();
        $dsQuocGia = NhaCungCap::select('Quoc_gia')->distinct()->pluck('Quoc_gia');

        return view('ketoan.nha-cung-cap.index', compact('nhaCungCaps', 'dsQuocGia'));
    }

    /**
     * KT-07: Khách hàng (chỉ xem)
     */
    public function khachHang(Request $request)
    {
        $query = KhachHang::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_khach_hang', 'like', "%{$search}%")
                    ->orWhere('Ten_khach_hang', 'like', "%{$search}%")
                    ->orWhere('So_dien_thoai', 'like', "%{$search}%")
                    ->orWhere('Ma_so_thue', 'like', "%{$search}%");
            });
        }

        if ($loai = $request->input('loai_khach_hang')) {
            if ($loai !== 'Tất cả') {
                $query->where('Loai_khach_hang', $loai);
            }
        }

        if ($trangThai = $request->input('trang_thai')) {
            if ($trangThai !== 'Tất cả') {
                $query->where('Trang_thai', $trangThai);
            }
        }

        $khachHangs = $query->orderBy('Ma_khach_hang', 'asc')->paginate(15)->withQueryString();

        return view('ketoan.khach-hang.index', compact('khachHangs'));
    }
}

