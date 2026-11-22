<?php

namespace App\Http\Controllers\QuanTri;

use App\Http\Controllers\Controller;
use App\Models\Kho;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NguoiDungController extends Controller
{
    /**
     * ADM-02: Danh sách người dùng (GET /quan-tri/nguoi-dung)
     */
    public function index(Request $request)
    {
        $query = NguoiDung::with(['vaiTro', 'kho']);

        // 1. Tìm kiếm (Họ tên, Tên đăng nhập, Email)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('Ho_ten', 'LIKE', "%{$search}%")
                  ->orWhere('Ten_dang_nhap', 'LIKE', "%{$search}%")
                  ->orWhere('Email', 'LIKE', "%{$search}%");
            });
        }

        // 2. Lọc theo Vai trò
        if ($vaiTro = $request->input('vai_tro')) {
            $query->where('Ma_vai_tro', $vaiTro);
        }

        // 3. Lọc theo Kho
        if ($kho = $request->input('kho')) {
            $query->where('Ma_kho', $kho);
        }

        // 4. Lọc theo Trạng thái
        if ($trangThai = $request->input('trang_thai')) {
            $query->where('Trang_thai', $trangThai);
        }

        $users = $query->orderBy('Ma_nguoi_dung', 'asc')->paginate(15)->withQueryString();

        $danhSachVaiTro = VaiTro::all();
        $danhSachKho = Kho::all();

        return view('quantri.nguoi-dung.index', compact(
            'users',
            'danhSachVaiTro',
            'danhSachKho'
        ));
    }

    /**
     * ADM-03: Màn hình Thêm người dùng (GET /quan-tri/nguoi-dung/them)
     */
    public function create()
    {
        $danhSachVaiTro = VaiTro::all();
        $danhSachKho = Kho::where('Trang_thai', 'Hoạt động')->get();
        $suggestedMa = CodeGenerator::generateMaNguoiDung();

        return view('quantri.nguoi-dung.form', [
            'mode' => 'Thêm',
            'user' => new NguoiDung(),
            'suggestedMa' => $suggestedMa,
            'danhSachVaiTro' => $danhSachVaiTro,
            'danhSachKho' => $danhSachKho,
        ]);
    }

    /**
     * Lưu người dùng mới (POST /quan-tri/nguoi-dung/them)
     */
    public function store(Request $request)
    {
        $request->validate([
            'Ho_ten' => 'required|string|max:100',
            'Ten_dang_nhap' => [
                'required',
                'string',
                'max:50',
                'unique:Nguoi_dung,Ten_dang_nhap',
                'regex:/^[a-zA-Z0-9_]+$/',
            ],
            'Email' => 'required|email|max:100|unique:Nguoi_dung,Email',
            'So_dien_thoai' => 'nullable|string|max:20',
            'Ma_vai_tro' => 'required|exists:Vai_tro,Ma_vai_tro',
            'Ma_kho' => 'nullable|exists:Kho,Ma_kho',
        ], [
            'Ho_ten.required' => 'Vui lòng nhập họ tên.',
            'Ten_dang_nhap.required' => 'Vui lòng nhập tên đăng nhập.',
            'Ten_dang_nhap.unique' => 'Tên đăng nhập này đã được sử dụng.',
            'Ten_dang_nhap.regex' => 'Tên đăng nhập chỉ gồm chữ không dấu, số và gạch dưới (không khoảng trắng).',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Email không đúng định dạng.',
            'Email.unique' => 'Email này đã được sử dụng.',
            'Ma_vai_tro.required' => 'Vui lòng chọn vai trò.',
            'Ma_vai_tro.exists' => 'Vai trò không hợp lệ.',
        ]);

        // Kiểm tra ràng buộc kho theo vai trò
        if (in_array($request->Ma_vai_tro, ['QL_KHO', 'NV_KHO']) && empty($request->Ma_kho)) {
            return back()->withErrors(['Ma_kho' => 'Bắt buộc phải chọn kho cho Quản lý kho hoặc Nhân viên kho.'])->withInput();
        }

        $maKho = in_array($request->Ma_vai_tro, ['QL_KHO', 'NV_KHO']) ? $request->Ma_kho : null;

        $tempPassword = 'SW@' . Str::random(6); // Mật khẩu tạm đáp ứng cả chữ và số

        DB::beginTransaction();
        try {
            $maNguoiDung = CodeGenerator::generateMaNguoiDung();

            $user = new NguoiDung();
            $user->Ma_nguoi_dung = $maNguoiDung;
            $user->Ten_dang_nhap = $request->Ten_dang_nhap;
            $user->Mat_khau = Hash::make($tempPassword);
            $user->Ho_ten = $request->Ho_ten;
            $user->Email = $request->Email;
            $user->So_dien_thoai = $request->So_dien_thoai;
            $user->Ma_vai_tro = $request->Ma_vai_tro;
            $user->Ma_kho = $maKho;
            $user->Trang_thai = 'Hoạt động';
            $user->Phai_doi_mat_khau = true;
            $user->So_lan_sai_mat_khau = 0;
            $user->save();

            ActivityLogger::log('Tạo', 'Người dùng', "Tạo mới tài khoản người dùng {$user->Ten_dang_nhap} ({$user->Ma_nguoi_dung})", $user);

            DB::commit();

            return redirect()->route('quantri.nguoi-dung.index')
                ->with('success', "Đã lưu người dùng {$user->Ma_nguoi_dung}. Mật khẩu tạm thời cấp cho người dùng: {$tempPassword}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * ADM-04: Màn hình Sửa người dùng (GET /quan-tri/nguoi-dung/{ma})
     */
    public function edit($ma)
    {
        $user = NguoiDung::with(['vaiTro', 'kho'])->where('Ma_nguoi_dung', $ma)->firstOrFail();
        $danhSachVaiTro = VaiTro::all();
        $danhSachKho = Kho::all();

        return view('quantri.nguoi-dung.form', [
            'mode' => 'Sửa',
            'user' => $user,
            'suggestedMa' => $user->Ma_nguoi_dung,
            'danhSachVaiTro' => $danhSachVaiTro,
            'danhSachKho' => $danhSachKho,
        ]);
    }

    /**
     * Cập nhật thông tin người dùng (POST /quan-tri/nguoi-dung/{ma})
     */
    public function update(Request $request, $ma)
    {
        $user = NguoiDung::where('Ma_nguoi_dung', $ma)->firstOrFail();

        $request->validate([
            'Ho_ten' => 'required|string|max:100',
            'Email' => "required|email|max:100|unique:Nguoi_dung,Email,{$ma},Ma_nguoi_dung",
            'So_dien_thoai' => 'nullable|string|max:20',
            'Ma_vai_tro' => 'required|exists:Vai_tro,Ma_vai_tro',
            'Ma_kho' => 'nullable|exists:Kho,Ma_kho',
        ], [
            'Ho_ten.required' => 'Vui lòng nhập họ tên.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.unique' => 'Email này đã được sử dụng bởi người dùng khác.',
            'Ma_vai_tro.required' => 'Vui lòng chọn vai trò.',
        ]);

        if (in_array($request->Ma_vai_tro, ['QL_KHO', 'NV_KHO']) && empty($request->Ma_kho)) {
            return back()->withErrors(['Ma_kho' => 'Bắt buộc phải chọn kho cho Quản lý kho hoặc Nhân viên kho.'])->withInput();
        }

        $maKho = in_array($request->Ma_vai_tro, ['QL_KHO', 'NV_KHO']) ? $request->Ma_kho : null;

        $changes = [];
        if ($user->Ho_ten !== $request->Ho_ten) $changes[] = "Họ tên: {$user->Ho_ten} -> {$request->Ho_ten}";
        if ($user->Email !== $request->Email) $changes[] = "Email: {$user->Email} -> {$request->Email}";
        if ($user->Ma_vai_tro !== $request->Ma_vai_tro) $changes[] = "Vai trò: {$user->Ma_vai_tro} -> {$request->Ma_vai_tro}";
        if ($user->Ma_kho !== $maKho) $changes[] = "Kho: {$user->Ma_kho} -> {$maKho}";

        $user->Ho_ten = $request->Ho_ten;
        $user->Email = $request->Email;
        $user->So_dien_thoai = $request->So_dien_thoai;
        $user->Ma_vai_tro = $request->Ma_vai_tro;
        $user->Ma_kho = $maKho;
        $user->save();

        ActivityLogger::log('Sửa', 'Người dùng', "Cập nhật thông tin tài khoản {$user->Ten_dang_nhap} (" . implode(', ', $changes) . ")", $user);

        return redirect()->route('quantri.nguoi-dung.index')->with('success', "Đã lưu người dùng {$user->Ma_nguoi_dung}.");
    }

    /**
     * Khóa hoặc Mở khóa tài khoản (POST /quan-tri/nguoi-dung/{ma}/toggle-lock)
     */
    public function toggleLock($ma)
    {
        $user = NguoiDung::where('Ma_nguoi_dung', $ma)->firstOrFail();

        // Không cho phép tự khóa tài khoản của chính mình
        if ($user->Ma_nguoi_dung === auth()->user()->Ma_nguoi_dung) {
            return back()->withErrors(['general' => 'Bạn không thể tự khóa tài khoản của chính mình.']);
        }

        if ($user->Trang_thai === 'Khóa') {
            $user->Trang_thai = 'Hoạt động';
            $user->So_lan_sai_mat_khau = 0; // Mở khóa thì reset số lần sai
            $user->save();

            ActivityLogger::log('Khóa / Mở khóa', 'Người dùng', "Mở khóa tài khoản {$user->Ten_dang_nhap} ({$user->Ma_nguoi_dung})", $user);
            $msg = "Đã mở khóa tài khoản {$user->Ten_dang_nhap}.";
        } else {
            $user->Trang_thai = 'Khóa';
            $user->save();

            ActivityLogger::log('Khóa / Mở khóa', 'Người dùng', "Khóa tài khoản {$user->Ten_dang_nhap} ({$user->Ma_nguoi_dung})", $user);
            $msg = "Đã khóa tài khoản {$user->Ten_dang_nhap}.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa tài khoản (DELETE /quan-tri/nguoi-dung/{ma})
     */
    public function destroy($ma)
    {
        $user = NguoiDung::where('Ma_nguoi_dung', $ma)->firstOrFail();

        // Không cho phép tự xóa chính mình
        if ($user->Ma_nguoi_dung === auth()->user()->Ma_nguoi_dung) {
            return back()->withErrors(['general' => 'Bạn không thể tự xóa tài khoản của chính mình.']);
        }

        // BR: Chỉ xóa được khi chưa phát sinh chứng từ nào tham chiếu tới
        $coPhatSinhDuLieu = DB::table('Don_dat_hang')->where('Ma_nguoi_lap', $ma)
            ->orWhere('Ma_nguoi_duyet', $ma)
            ->orWhere('Ma_nguoi_gui_ncc', $ma)
            ->orWhere('Ma_nguoi_huy', $ma)
            ->orWhere('Ma_nguoi_dong_thieu', $ma)
            ->exists()
            || DB::table('Phieu_nhap_kho')->where('Ma_nguoi_lap', $ma)->exists()
            || DB::table('Don_hang_ban')->where('Ma_nguoi_lap', $ma)->exists()
            || DB::table('Phieu_xuat_kho')->where('Ma_nguoi_xuat', $ma)->exists()
            || DB::table('Chi_tiet_phieu_xuat')->where('Ma_nguoi_doi_lo', $ma)->exists()
            || DB::table('Phieu_kiem_ke')->where('Ma_nguoi_tao', $ma)
                ->orWhere('Ma_nguoi_kiem', $ma)
                ->orWhere('Ma_nguoi_duyet', $ma)
                ->exists();

        if ($coPhatSinhDuLieu) {
            return back()->withErrors([
                'general' => "Không xóa được vì {$user->Ho_ten} đã phát sinh dữ liệu. Chuyển trạng thái sang Khóa.",
            ]);
        }

        $ten = $user->Ten_dang_nhap;
        $user->delete();

        ActivityLogger::log('Hủy', 'Người dùng', "Xóa tài khoản người dùng {$ten} ({$ma})", null);

        return redirect()->route('quantri.nguoi-dung.index')->with('success', "Đã xóa người dùng {$ma}.");
    }
}

