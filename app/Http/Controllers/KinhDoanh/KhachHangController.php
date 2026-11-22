<?php

namespace App\Http\Controllers\KinhDoanh;

use App\Http\Controllers\Controller;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KhachHangController extends Controller
{
    public function index(Request $request)
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

        $khachHangs = $query->orderBy('Ma_khach_hang', 'desc')->paginate(15)->withQueryString();

        $selectedKh = null;
        if ($ma = $request->input('ma')) {
            $selectedKh = KhachHang::where('Ma_khach_hang', $ma)->first();
        }

        $nextCode = CodeGenerator::generateMaKhachHang();

        return view('kinhdoanh.khach-hang.index', compact('khachHangs', 'selectedKh', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Ten_khach_hang' => 'required|string|max:255',
            'Loai_khach_hang' => 'required|in:Cá nhân,Doanh nghiệp',
            'Ma_so_thue' => 'nullable|string|max:20',
            'So_dien_thoai' => 'required|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Dia_chi' => 'nullable|string|max:255',
            'Trang_thai' => 'required|in:Đang giao dịch,Ngừng giao dịch',
        ], [
            'Ten_khach_hang.required' => 'Vui lòng nhập tên khách hàng',
            'Loai_khach_hang.required' => 'Vui lòng chọn loại khách hàng',
            'So_dien_thoai.required' => 'Vui lòng nhập số điện thoại',
            'Email.email' => 'Email không đúng định dạng',
        ]);

        $maKh = CodeGenerator::generateMaKhachHang();

        $kh = DB::transaction(function () use ($validated, $maKh) {
            $data = array_merge($validated, ['Ma_khach_hang' => $maKh]);
            return KhachHang::create($data);
        });

        ActivityLogger::log(
            'Tạo',
            'Danh mục',
            "Thêm mới khách hàng: {$kh->Ten_khach_hang} ({$kh->Ma_khach_hang})",
            $kh
        );

        return redirect()->route('kinh-doanh.khach-hang.index', ['ma' => $kh->Ma_khach_hang])
            ->with('success', "Đã thêm khách hàng {$kh->Ma_khach_hang} thành công.");
    }

    public function update(Request $request, string $ma)
    {
        $kh = KhachHang::where('Ma_khach_hang', $ma)->firstOrFail();

        $validated = $request->validate([
            'Ten_khach_hang' => 'required|string|max:255',
            'Loai_khach_hang' => 'required|in:Cá nhân,Doanh nghiệp',
            'Ma_so_thue' => 'nullable|string|max:20',
            'So_dien_thoai' => 'required|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Dia_chi' => 'nullable|string|max:255',
            'Trang_thai' => 'required|in:Đang giao dịch,Ngừng giao dịch',
        ]);

        $kh->update($validated);

        ActivityLogger::log(
            'Sửa',
            'Danh mục',
            "Cập nhật thông tin khách hàng {$kh->Ma_khach_hang}",
            $kh
        );

        return redirect()->route('kinh-doanh.khach-hang.index', ['ma' => $kh->Ma_khach_hang])
            ->with('success', "Đã cập nhật khách hàng {$kh->Ma_khach_hang} thành công.");
    }

    public function destroy(string $ma)
    {
        $kh = KhachHang::where('Ma_khach_hang', $ma)->firstOrFail();

        $hasOrders = DonHangBan::where('Ma_khach_hang', $ma)->exists();
        if ($hasOrders) {
            return redirect()->route('kinh-doanh.khach-hang.index', ['ma' => $ma])
                ->with('error', 'Chỉ xóa được khi khách chưa có đơn hàng nào. Khách hàng đã phát sinh dữ liệu vui lòng chuyển trạng thái sang Ngừng giao dịch.');
        }

        $tenKh = $kh->Ten_khach_hang;
        $kh->delete();

        ActivityLogger::log(
            'Xóa',
            'Danh mục',
            "Xóa khách hàng {$tenKh} ({$ma})",
            null
        );

        return redirect()->route('kinh-doanh.khach-hang.index')
            ->with('success', "Đã xóa khách hàng {$ma} thành công.");
    }
}

