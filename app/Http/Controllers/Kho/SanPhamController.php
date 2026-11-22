<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonBan;
use App\Models\ChiTietDonDatHang;
use App\Models\Lo;
use App\Models\SanPham;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanPhamController extends Controller
{
    public function index(Request $request)
    {
        $query = SanPham::query();

        // Tìm kiếm: mã, tên sản phẩm, nhà sản xuất
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_san_pham', 'like', "%{$search}%")
                    ->orWhere('Ten_san_pham', 'like', "%{$search}%")
                    ->orWhere('Nha_san_xuat', 'like', "%{$search}%");
            });
        }

        // Lọc theo loại vang
        if ($loaiVang = $request->input('loai_vang')) {
            if ($loaiVang !== 'Tất cả') {
                $query->where('Loai_vang', $loaiVang);
            }
        }

        // Lọc theo quốc gia
        if ($quocGia = $request->input('quoc_gia')) {
            if ($quocGia !== 'Tất cả') {
                $query->where('Quoc_gia', $quocGia);
            }
        }

        // Lọc theo trạng thái
        if ($trangThai = $request->input('trang_thai')) {
            if ($trangThai !== 'Tất cả') {
                $query->where('Trang_thai', $trangThai);
            }
        }

        $sanPhams = $query->orderBy('Ma_san_pham', 'desc')->paginate(15)->withQueryString();

        // Danh sách quốc gia có trong hệ thống để đổ vào dropdown filter
        $dsQuocGia = SanPham::select('Quoc_gia')->distinct()->pluck('Quoc_gia');

        // Sản phẩm đang được chọn để xem/sửa (nếu có)
        $selectedSp = null;
        if ($ma = $request->input('ma')) {
            $selectedSp = SanPham::where('Ma_san_pham', $ma)->first();
        }

        $nextCode = CodeGenerator::generateMaSanPham();

        return view('kho.san-pham.index', compact('sanPhams', 'dsQuocGia', 'selectedSp', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Ten_san_pham' => 'required|string|max:255',
            'Loai_vang' => 'required|in:Đỏ,Trắng,Hồng,Vang nổ,Khác',
            'Nien_vu' => 'nullable|integer|min:1900|max:2100',
            'Quoc_gia' => 'required|string|max:100',
            'Vung' => 'nullable|string|max:100',
            'Nha_san_xuat' => 'required|string|max:255',
            'Dung_tich' => 'required|integer|min:1',
            'Nong_do_con' => 'nullable|numeric|min:0|max:100',
            'Don_vi_tinh' => 'required|string|max:20',
            'Quy_cach_thung' => 'nullable|integer|min:1',
            'Trang_thai' => 'required|in:Đang kinh doanh,Ngừng',
        ], [
            'Ten_san_pham.required' => 'Vui lòng nhập tên sản phẩm',
            'Loai_vang.required' => 'Vui lòng chọn loại vang',
            'Quoc_gia.required' => 'Vui lòng nhập quốc gia',
            'Nha_san_xuat.required' => 'Vui lòng nhập nhà sản xuất',
            'Dung_tich.required' => 'Vui lòng nhập dung tích',
            'Dung_tich.min' => 'Dung tích phải lớn hơn 0',
        ]);

        $maSanPham = CodeGenerator::generateMaSanPham();

        $sp = DB::transaction(function () use ($validated, $maSanPham) {
            $data = array_merge($validated, [
                'Ma_san_pham' => $maSanPham,
                'Gia_von_binh_quan' => 0.00,
                'Gia_niem_yet' => null,
            ]);

            return SanPham::create($data);
        });

        ActivityLogger::log(
            'Tạo',
            'Danh mục',
            "Thêm mới sản phẩm: {$sp->Ten_san_pham} ({$sp->Ma_san_pham})",
            $sp
        );

        return redirect()->route('kho.san-pham.index', ['ma' => $sp->Ma_san_pham])
            ->with('success', "Đã thêm sản phẩm {$sp->Ma_san_pham} thành công.");
    }

    public function update(Request $request, string $ma)
    {
        $sp = SanPham::where('Ma_san_pham', $ma)->firstOrFail();

        $validated = $request->validate([
            'Ten_san_pham' => 'required|string|max:255',
            'Loai_vang' => 'required|in:Đỏ,Trắng,Hồng,Vang nổ,Khác',
            'Nien_vu' => 'nullable|integer|min:1900|max:2100',
            'Quoc_gia' => 'required|string|max:100',
            'Vung' => 'nullable|string|max:100',
            'Nha_san_xuat' => 'required|string|max:255',
            'Dung_tich' => 'required|integer|min:1',
            'Nong_do_con' => 'nullable|numeric|min:0|max:100',
            'Don_vi_tinh' => 'required|string|max:20',
            'Quy_cach_thung' => 'nullable|integer|min:1',
            'Trang_thai' => 'required|in:Đang kinh doanh,Ngừng',
        ]);

        $sp->update($validated);

        ActivityLogger::log(
            'Sửa',
            'Danh mục',
            "Cập nhật thông tin sản phẩm {$sp->Ma_san_pham}",
            $sp
        );

        return redirect()->route('kho.san-pham.index', ['ma' => $sp->Ma_san_pham])
            ->with('success', "Đã cập nhật sản phẩm {$sp->Ma_san_pham} thành công.");
    }

    public function destroy(string $ma)
    {
        $sp = SanPham::where('Ma_san_pham', $ma)->firstOrFail();

        // Kiểm tra ràng buộc: chỉ xóa được khi chưa có lô và chưa có chứng từ nào
        $hasLo = Lo::where('Ma_san_pham', $ma)->exists();
        $hasDonDatHang = ChiTietDonDatHang::where('Ma_san_pham', $ma)->exists();
        $hasDonBan = ChiTietDonBan::where('Ma_san_pham', $ma)->exists();

        if ($hasLo || $hasDonDatHang || $hasDonBan) {
            return redirect()->route('kho.san-pham.index', ['ma' => $ma])
                ->with('error', 'Chỉ xóa được khi sản phẩm chưa có lô, chứng từ. Sản phẩm đã phát sinh dữ liệu vui lòng chuyển trạng thái sang Ngừng.');
        }

        $tenSp = $sp->Ten_san_pham;
        $sp->delete();

        ActivityLogger::log(
            'Xóa',
            'Danh mục',
            "Xóa sản phẩm {$tenSp} ({$ma})",
            null
        );

        return redirect()->route('kho.san-pham.index')
            ->with('success', "Đã xóa sản phẩm {$ma} thành công.");
    }
}

