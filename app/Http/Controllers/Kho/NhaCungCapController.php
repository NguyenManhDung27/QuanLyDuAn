<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\DonDatHang;
use App\Models\NhaCungCap;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NhaCungCapController extends Controller
{
    public function index(Request $request)
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

        $nhaCungCaps = $query->orderBy('Ma_ncc', 'desc')->paginate(15)->withQueryString();

        $dsQuocGia = NhaCungCap::select('Quoc_gia')->distinct()->pluck('Quoc_gia');

        $selectedNcc = null;
        if ($ma = $request->input('ma')) {
            $selectedNcc = NhaCungCap::where('Ma_ncc', $ma)->first();
        }

        $nextCode = CodeGenerator::generateMaNhaCungCap();

        return view('kho.nha-cung-cap.index', compact('nhaCungCaps', 'dsQuocGia', 'selectedNcc', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Ten_ncc' => 'required|string|max:255',
            'Quoc_gia' => 'required|string|max:100',
            'Dia_chi' => 'nullable|string|max:255',
            'Ma_so_thue' => 'nullable|string|max:20',
            'Nguoi_lien_he' => 'nullable|string|max:100',
            'So_dien_thoai' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Trang_thai' => 'required|in:Đang giao dịch,Ngừng giao dịch',
        ], [
            'Ten_ncc.required' => 'Vui lòng nhập tên nhà cung cấp',
            'Quoc_gia.required' => 'Vui lòng nhập quốc gia',
            'Email.email' => 'Email không đúng định dạng',
        ]);

        $maNcc = CodeGenerator::generateMaNhaCungCap();

        $ncc = DB::transaction(function () use ($validated, $maNcc) {
            $data = array_merge($validated, ['Ma_ncc' => $maNcc]);
            return NhaCungCap::create($data);
        });

        ActivityLogger::log(
            'Tạo',
            'Danh mục',
            "Thêm mới nhà cung cấp: {$ncc->Ten_ncc} ({$ncc->Ma_ncc})",
            $ncc
        );

        return redirect()->route('kho.nha-cung-cap.index', ['ma' => $ncc->Ma_ncc])
            ->with('success', "Đã thêm nhà cung cấp {$ncc->Ma_ncc} thành công.");
    }

    public function update(Request $request, string $ma)
    {
        $ncc = NhaCungCap::where('Ma_ncc', $ma)->firstOrFail();

        $validated = $request->validate([
            'Ten_ncc' => 'required|string|max:255',
            'Quoc_gia' => 'required|string|max:100',
            'Dia_chi' => 'nullable|string|max:255',
            'Ma_so_thue' => 'nullable|string|max:20',
            'Nguoi_lien_he' => 'nullable|string|max:100',
            'So_dien_thoai' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Trang_thai' => 'required|in:Đang giao dịch,Ngừng giao dịch',
        ]);

        $ncc->update($validated);

        ActivityLogger::log(
            'Sửa',
            'Danh mục',
            "Cập nhật thông tin nhà cung cấp {$ncc->Ma_ncc}",
            $ncc
        );

        return redirect()->route('kho.nha-cung-cap.index', ['ma' => $ncc->Ma_ncc])
            ->with('success', "Đã cập nhật nhà cung cấp {$ncc->Ma_ncc} thành công.");
    }

    public function destroy(string $ma)
    {
        $ncc = NhaCungCap::where('Ma_ncc', $ma)->firstOrFail();

        // Kiểm tra xem đã có đơn đặt hàng nào chưa
        $hasOrders = DonDatHang::where('Ma_ncc', $ma)->exists();
        if ($hasOrders) {
            return redirect()->route('kho.nha-cung-cap.index', ['ma' => $ma])
                ->with('error', 'Chỉ xóa được khi chưa có đề xuất, đơn đặt hàng nào. Nhà cung cấp đã phát sinh dữ liệu vui lòng chuyển trạng thái sang Ngừng giao dịch.');
        }

        $tenNcc = $ncc->Ten_ncc;
        $ncc->delete();

        ActivityLogger::log(
            'Xóa',
            'Danh mục',
            "Xóa nhà cung cấp {$tenNcc} ({$ma})",
            null
        );

        return redirect()->route('kho.nha-cung-cap.index')
            ->with('success', "Đã xóa nhà cung cấp {$ma} thành công.");
    }
}

