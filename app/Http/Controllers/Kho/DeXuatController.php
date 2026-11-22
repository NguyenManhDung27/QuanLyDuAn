<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonDatHang;
use App\Models\DonDatHang;
use App\Models\Kho;
use App\Models\NhaCungCap;
use App\Models\SanPham;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeXuatController extends Controller
{
    /**
     * QLK-03: Danh sách đề xuất nhập kho
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $query = DonDatHang::with(['nhaCungCap', 'nguoiLap'])
            ->where('Ma_kho', $maKho)
            ->whereIn('Trang_thai', ['Đang cập nhật', 'Chờ phê duyệt', 'Từ chối', 'Đề xuất đã hủy']);

        // Tab lọc theo trạng thái
        $statusTab = $request->input('tab', 'Tất cả');
        if ($statusTab !== 'Tất cả') {
            $query->where('Trang_thai', $statusTab);
        }

        // Lọc từ khóa tìm kiếm (Mã đề xuất, Tên nhà cung cấp)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_don_dat_hang', 'like', "%{$search}%")
                  ->orWhereHas('nhaCungCap', function ($sub) use ($search) {
                      $sub->where('Ten_ncc', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo ngày lập
        if ($from = $request->input('tu_ngay')) {
            $query->whereDate('Ngay_lap', '>=', $from);
        }
        if ($to = $request->input('den_ngay')) {
            $query->whereDate('Ngay_lap', '<=', $to);
        }

        $proposals = $query->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_don_dat_hang', 'desc')
            ->get();

        // Đếm số lượng cho từng tab
        $counts = [
            'Tất cả' => DonDatHang::where('Ma_kho', $maKho)
                ->whereIn('Trang_thai', ['Đang cập nhật', 'Chờ phê duyệt', 'Từ chối', 'Đề xuất đã hủy'])
                ->count(),
            'Đang cập nhật' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đang cập nhật')->count(),
            'Chờ phê duyệt' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Chờ phê duyệt')->count(),
            'Từ chối' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Từ chối')->count(),
            'Đề xuất đã hủy' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đề xuất đã hủy')->count(),
        ];

        return view('kho.de-xuat.index', [
            'proposals' => $proposals,
            'statusTab' => $statusTab,
            'counts' => $counts,
            'filters' => [
                'search' => $search,
                'tu_ngay' => $request->input('tu_ngay', ''),
                'den_ngay' => $request->input('den_ngay', ''),
            ],
        ]);
    }

    /**
     * QLK-04: Form tạo đề xuất nhập kho
     */
    public function create(): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';
        $kho = Kho::find($maKho);

        $nhacungcaps = NhaCungCap::where('Trang_thai', 'Đang giao dịch')
            ->orderBy('Ten_ncc')
            ->get();

        $sanphams = SanPham::where('Trang_thai', 'Đang kinh doanh')
            ->orderBy('Ten_san_pham')
            ->get();

        return view('kho.de-xuat.create', [
            'kho' => $kho,
            'nhacungcaps' => $nhacungcaps,
            'sanphams' => $sanphams,
        ]);
    }

    /**
     * Lưu đề xuất mới
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $action = $request->input('action', 'save'); // 'save' hoặc 'send'

        $request->validate([
            'Ma_ncc' => 'required|exists:Nha_cung_cap,Ma_ncc',
            'Ngay_du_kien_nhan_du' => 'required|date',
            'Ghi_chu_phe_duyet' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.Ma_san_pham' => 'required|exists:San_pham,Ma_san_pham',
            'items.*.So_luong_dat' => 'required|integer|min:1',
            'items.*.Don_gia_mua' => 'required|numeric|min:0',
        ], [
            'Ma_ncc.required' => 'Vui lòng chọn nhà cung cấp.',
            'Ngay_du_kien_nhan_du.required' => 'Vui lòng chọn ngày dự kiến nhận đủ.',
            'items.required' => 'Đề xuất phải có ít nhất một dòng sản phẩm.',
            'items.min' => 'Đề xuất phải có ít nhất một dòng sản phẩm.',
            'items.*.So_luong_dat.min' => 'Số lượng đặt phải lớn hơn 0.',
            'items.*.Don_gia_mua.min' => 'Đơn giá mua không được âm.',
        ]);

        // Kiểm tra mỗi sản phẩm chỉ xuất hiện 1 lần trên đơn
        $spIds = array_column($request->input('items', []), 'Ma_san_pham');
        if (count($spIds) !== count(array_unique($spIds))) {
            return back()->withInput()->with('error', 'Một sản phẩm không được xuất hiện hai lần trên cùng một đề xuất.');
        }

        $trangThai = ($action === 'send') ? 'Chờ phê duyệt' : 'Đang cập nhật';

        return DB::transaction(function () use ($request, $user, $maKho, $trangThai, $action) {
            $maDon = CodeGenerator::generateMaDonDatHang();

            $tongGiaTri = 0;
            foreach ($request->input('items', []) as $item) {
                $tongGiaTri += (int) $item['So_luong_dat'] * (float) $item['Don_gia_mua'];
            }

            $don = DonDatHang::create([
                'Ma_don_dat_hang' => $maDon,
                'Ma_ncc' => $request->input('Ma_ncc'),
                'Ma_kho' => $maKho,
                'Ma_nguoi_lap' => $user->Ma_nguoi_dung,
                'Ngay_lap' => now()->toDateString(),
                'Ngay_du_kien_nhan_du' => $request->input('Ngay_du_kien_nhan_du'),
                'Trang_thai' => $trangThai,
                'Tong_gia_tri' => $tongGiaTri,
                'Ghi_chu_phe_duyet' => $request->input('Ghi_chu_phe_duyet'),
            ]);

            foreach ($request->input('items', []) as $item) {
                ChiTietDonDatHang::create([
                    'Ma_don_dat_hang' => $maDon,
                    'Ma_san_pham' => $item['Ma_san_pham'],
                    'So_luong_dat' => $item['So_luong_dat'],
                    'Don_gia_mua' => $item['Don_gia_mua'],
                ]);
            }

            if ($action === 'send') {
                ActivityLogger::log('Gửi duyệt đề xuất', "Gửi đề xuất nhập kho {$maDon} cho Kế toán duyệt", $maDon);
                return redirect()->route('kho.de-xuat.index')->with('success', "Đã gửi đề xuất {$maDon} cho Kế toán.");
            } else {
                ActivityLogger::log('Tạo đề xuất', "Tạo mới đề xuất nhập kho {$maDon}", $maDon);
                return redirect()->route('kho.de-xuat.edit', $maDon)->with('success', "Đã lưu đề xuất {$maDon}.");
            }
        });
    }

    /**
     * QLK-04: Xem chi tiết hoặc chuyển hướng sửa
     */
    public function show(string $ma): View|RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $don = DonDatHang::with(['nhaCungCap', 'kho', 'nguoiLap', 'nguoiDuyet', 'nguoiGuiNcc', 'chiTiets.sanPham'])
            ->where('Ma_kho', $maKho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        // Nếu là Đang cập nhật hoặc Từ chối thì chuyển sang form edit
        if (in_array($don->Trang_thai, ['Đang cập nhật', 'Từ chối'])) {
            return redirect()->route('kho.de-xuat.edit', $ma);
        }

        // Tiến độ nhận hàng
        $tienDoMap = $don->getTienDoChiTietMap();
        $receipts = $don->phieuNhapKhos()
            ->with('nguoiLap')
            ->where('Trang_thai', 'Đã nhập kho')
            ->orderBy('Ngay_lap', 'desc')
            ->get();

        return view('kho.de-xuat.show', [
            'don' => $don,
            'tienDoMap' => $tienDoMap,
            'receipts' => $receipts,
        ]);
    }

    /**
     * QLK-04: Sửa đề xuất (Đang cập nhật hoặc Từ chối)
     */
    public function edit(string $ma): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $don = DonDatHang::with(['chiTiets.sanPham'])
            ->where('Ma_kho', $maKho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        $nhacungcaps = NhaCungCap::where('Trang_thai', 'Đang giao dịch')->orderBy('Ten_ncc')->get();
        $sanphams = SanPham::where('Trang_thai', 'Đang kinh doanh')->orderBy('Ten_san_pham')->get();

        return view('kho.de-xuat.edit', [
            'don' => $don,
            'kho' => Kho::find($maKho),
            'nhacungcaps' => $nhacungcaps,
            'sanphams' => $sanphams,
        ]);
    }

    /**
     * Cập nhật đề xuất (Đang cập nhật hoặc Từ chối)
     */
    public function update(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $don = DonDatHang::where('Ma_kho', $maKho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if (!in_array($don->Trang_thai, ['Đang cập nhật', 'Từ chối'])) {
            return back()->with('error', 'Không thể sửa nội dung đề xuất ở trạng thái hiện tại.');
        }

        $action = $request->input('action', 'save'); // 'save' hoặc 'send'

        $request->validate([
            'Ma_ncc' => 'required|exists:Nha_cung_cap,Ma_ncc',
            'Ngay_du_kien_nhan_du' => 'required|date',
            'Ghi_chu_phe_duyet' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.Ma_san_pham' => 'required|exists:San_pham,Ma_san_pham',
            'items.*.So_luong_dat' => 'required|integer|min:1',
            'items.*.Don_gia_mua' => 'required|numeric|min:0',
        ]);

        $spIds = array_column($request->input('items', []), 'Ma_san_pham');
        if (count($spIds) !== count(array_unique($spIds))) {
            return back()->withInput()->with('error', 'Một sản phẩm không được xuất hiện hai lần trên cùng một đề xuất.');
        }

        return DB::transaction(function () use ($request, $don, $action, $ma) {
            $tongGiaTri = 0;
            foreach ($request->input('items', []) as $item) {
                $tongGiaTri += (int) $item['So_luong_dat'] * (float) $item['Don_gia_mua'];
            }

            $updateData = [
                'Ma_ncc' => $request->input('Ma_ncc'),
                'Ngay_du_kien_nhan_du' => $request->input('Ngay_du_kien_nhan_du'),
                'Tong_gia_tri' => $tongGiaTri,
                'Ghi_chu_phe_duyet' => $request->input('Ghi_chu_phe_duyet'),
            ];

            if ($action === 'send') {
                $updateData['Trang_thai'] = 'Chờ phê duyệt';
                $updateData['Ly_do_tu_choi'] = null; // Xóa lý do từ chối cũ nếu gửi lại
            }

            $don->update($updateData);

            // Cập nhật chi tiết
            ChiTietDonDatHang::where('Ma_don_dat_hang', $ma)->delete();
            foreach ($request->input('items', []) as $item) {
                ChiTietDonDatHang::create([
                    'Ma_don_dat_hang' => $ma,
                    'Ma_san_pham' => $item['Ma_san_pham'],
                    'So_luong_dat' => $item['So_luong_dat'],
                    'Don_gia_mua' => $item['Don_gia_mua'],
                ]);
            }

            if ($action === 'send') {
                ActivityLogger::log('Gửi duyệt đề xuất', "Gửi lại đề xuất {$ma} cho Kế toán duyệt", $ma);
                return redirect()->route('kho.de-xuat.index')->with('success', "Đã gửi đề xuất {$ma} cho Kế toán.");
            } else {
                ActivityLogger::log('Cập nhật đề xuất', "Cập nhật đề xuất {$ma}", $ma);
                return back()->with('success', "Đã lưu đề xuất {$ma}.");
            }
        });
    }

    /**
     * Gửi duyệt đề xuất đang cập nhật
     */
    public function guiDuyet(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Đang cập nhật') {
            return back()->with('error', 'Chỉ có thể gửi duyệt đề xuất ở trạng thái Đang cập nhật.');
        }

        if (empty($don->Ngay_du_kien_nhan_du)) {
            return back()->with('error', 'Vui lòng nhập ngày dự kiến nhận đủ trước khi gửi duyệt.');
        }

        $don->update(['Trang_thai' => 'Chờ phê duyệt']);
        ActivityLogger::log('Gửi duyệt đề xuất', "Gửi đề xuất {$ma} cho Kế toán duyệt", $ma);

        return redirect()->route('kho.de-xuat.index')->with('success', "Đã gửi đề xuất {$ma} cho Kế toán.");
    }

    /**
     * Thu hồi đề xuất chờ duyệt về Đang cập nhật
     */
    public function thuHoi(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Chờ phê duyệt') {
            return back()->with('error', 'Chỉ có thể thu hồi đề xuất ở trạng thái Chờ phê duyệt.');
        }

        $don->update(['Trang_thai' => 'Đang cập nhật']);
        ActivityLogger::log('Thu hồi đề xuất', "Thu hồi đề xuất {$ma} về Đang cập nhật", $ma);

        return redirect()->route('kho.de-xuat.edit', $ma)->with('success', "Đã thu hồi đề xuất {$ma}. Bạn có thể tiếp tục chỉnh sửa.");
    }

    /**
     * Gửi lại đề xuất bị từ chối
     */
    public function guiLai(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Từ chối') {
            return back()->with('error', 'Chỉ có thể gửi lại đề xuất ở trạng thái Từ chối.');
        }

        $don->update([
            'Trang_thai' => 'Chờ phê duyệt',
            'Ly_do_tu_choi' => null,
        ]);

        ActivityLogger::log('Gửi lại đề xuất', "Gửi lại đề xuất {$ma} cho Kế toán duyệt", $ma);

        return redirect()->route('kho.de-xuat.index')->with('success', "Đã gửi đề xuất {$ma} cho Kế toán.");
    }

    /**
     * Xóa đề xuất (chỉ khi Đang cập nhật)
     */
    public function destroy(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Đang cập nhật') {
            return back()->with('error', 'Chỉ có thể xóa đề xuất ở trạng thái Đang cập nhật.');
        }

        DB::transaction(function () use ($don, $ma) {
            ChiTietDonDatHang::where('Ma_don_dat_hang', $ma)->delete();
            $don->delete();
            ActivityLogger::log('Xóa đề xuất', "Xóa đề xuất {$ma}", $ma);
        });

        return redirect()->route('kho.de-xuat.index')->with('success', "Đã xóa đề xuất {$ma}.");
    }

    /**
     * Hủy đề xuất bị từ chối -> Đề xuất đã hủy
     */
    public function huyDeXuat(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Từ chối') {
            return back()->with('error', 'Chỉ có thể hủy đề xuất ở trạng thái Từ chối.');
        }

        $request->validate([
            'ly_do_huy' => 'required|string|max:1000',
        ], [
            'ly_do_huy.required' => 'Vui lòng nhập lý do hủy đề xuất.',
        ]);

        $don->update([
            'Trang_thai' => 'Đề xuất đã hủy',
            'Ma_nguoi_huy' => $user->Ma_nguoi_dung,
            'Thoi_diem_huy' => now(),
            'Ly_do_huy' => $request->input('ly_do_huy'),
        ]);

        ActivityLogger::log('Hủy đề xuất', "Hủy đề xuất {$ma}: {$request->input('ly_do_huy')}", $ma);

        return redirect()->route('kho.de-xuat.index')->with('success', "Đã hủy đề xuất {$ma}.");
    }
}

