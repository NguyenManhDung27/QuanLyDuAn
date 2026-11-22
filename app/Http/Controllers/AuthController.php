<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'Ten_dang_nhap' => 'required|string',
            'Mat_khau' => 'required|string',
        ], [
            'Ten_dang_nhap.required' => 'Vui lòng nhập tên đăng nhập.',
            'Mat_khau.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = NguoiDung::where('Ten_dang_nhap', $request->Ten_dang_nhap)->first();

        if (!$user) {
            return back()->withErrors([
                'Ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ])->withInput($request->only('Ten_dang_nhap'));
        }

        if ($user->Trang_thai === 'Khóa') {
            ActivityLogger::log('Đăng nhập sai', 'Người dùng', "Đăng nhập thất bại: Tài khoản đang bị khóa ({$user->Ten_dang_nhap})", $user);
            return back()->withErrors([
                'Ten_dang_nhap' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên hoặc dùng tính năng Quên mật khẩu.',
            ])->withInput($request->only('Ten_dang_nhap'));
        }

        if (!Hash::check($request->Mat_khau, $user->Mat_khau)) {
            $user->So_lan_sai_mat_khau = ($user->So_lan_sai_mat_khau ?? 0) + 1;

            if ($user->So_lan_sai_mat_khau >= 5) {
                $user->Trang_thai = 'Khóa';
                $user->save();

                ActivityLogger::log('Khóa', 'Người dùng', "Tự động khóa tài khoản {$user->Ten_dang_nhap} do nhập sai mật khẩu 5 lần liên tiếp", $user);

                return back()->withErrors([
                    'Ten_dang_nhap' => 'Bạn đã nhập sai mật khẩu 5 lần liên tiếp. Tài khoản đã bị khóa để bảo vệ hệ thống.',
                ])->withInput($request->only('Ten_dang_nhap'));
            }

            $user->save();
            $conLai = 5 - $user->So_lan_sai_mat_khau;

            ActivityLogger::log('Đăng nhập sai', 'Người dùng', "Nhập sai mật khẩu lần {$user->So_lan_sai_mat_khau} cho tài khoản {$user->Ten_dang_nhap}", $user);

            return back()->withErrors([
                'Ten_dang_nhap' => "Mật khẩu không chính xác. Bạn còn {$conLai} lần thử trước khi tài khoản bị khóa.",
            ])->withInput($request->only('Ten_dang_nhap'));
        }

        // Đăng nhập thành công -> Reset số lần sai mật khẩu
        $user->So_lan_sai_mat_khau = 0;
        $user->save();

        Auth::login($user, false);
        $request->session()->regenerate();

        ActivityLogger::log('Đăng nhập', 'Người dùng', "Đăng nhập thành công vào hệ thống", $user);

        if ($user->Phai_doi_mat_khau) {
            return redirect()->route('auth.doi-mat-khau-lan-dau')
                ->with('warning', 'Đây là lần đăng nhập đầu tiên. Bạn cần đổi mật khẩu trước khi tiếp tục.');
        }

        return $this->redirectByRole($user);
    }

    public function showFirstLoginChangePassword()
    {
        return view('auth.doi-mat-khau-lan-dau');
    }

    public function updateFirstLoginPassword(Request $request)
    {
        $request->validate([
            'mat_khau_cu' => 'required',
            'mat_khau_moi' => [
                'required',
                'string',
                'min:8',
                'different:mat_khau_cu',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)/',
            ],
        ], [
            'mat_khau_cu.required' => 'Vui lòng nhập mật khẩu tạm.',
            'mat_khau_moi.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'mat_khau_moi.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'mat_khau_moi.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'mat_khau_moi.regex' => 'Mật khẩu mới phải bao gồm cả chữ và số.',
        ]);

        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();

        if (!Hash::check($request->mat_khau_cu, $user->Mat_khau)) {
            return back()->withErrors(['mat_khau_cu' => 'Mật khẩu tạm không chính xác.']);
        }

        $user->Mat_khau = Hash::make($request->mat_khau_moi);
        $user->Phai_doi_mat_khau = false;
        $user->save();

        ActivityLogger::log('Sửa', 'Người dùng', "Đổi mật khẩu lần đầu thành công", $user);

        return $this->redirectByRole($user)->with('success', 'Đổi mật khẩu lần đầu thành công! Chào mừng bạn đến với hệ thống Soul Wine.');
    }

    public function showChangePassword()
    {
        return view('auth.doi-mat-khau');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'mat_khau_hien_tai' => 'required',
            'mat_khau_moi' => [
                'required',
                'string',
                'min:8',
                'different:mat_khau_hien_tai',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)/',
            ],
        ], [
            'mat_khau_hien_tai.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'mat_khau_moi.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'mat_khau_moi.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'mat_khau_moi.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'mat_khau_moi.regex' => 'Mật khẩu mới phải bao gồm cả chữ và số.',
        ]);

        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();

        if (!Hash::check($request->mat_khau_hien_tai, $user->Mat_khau)) {
            return back()->withErrors(['mat_khau_hien_tai' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->Mat_khau = Hash::make($request->mat_khau_moi);
        $user->save();

        ActivityLogger::log('Sửa', 'Người dùng', "Đổi mật khẩu tài khoản thành công", $user);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log('Đăng xuất', 'Người dùng', "Đăng xuất khỏi hệ thống", $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi hệ thống.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.quen-mat-khau');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['Email' => 'required|email'], [
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Email không đúng định dạng.',
        ]);

        $status = Password::sendResetLink($request->only('Email'));

        if ($status === Password::RESET_LINK_SENT) {
            ActivityLogger::log('Sửa', 'Người dùng', "Yêu cầu gửi liên kết đặt lại mật khẩu đến email {$request->Email}");
            return back()->with('status', 'Đường dẫn đặt lại mật khẩu đã được gửi đến email của bạn.');
        }

        return back()->withErrors(['Email' => 'Không tìm thấy người dùng có địa chỉ email này.']);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.dat-lai-mat-khau')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'Email' => 'required|email',
            'Mat_khau' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)/',
            ],
        ], [
            'Mat_khau.required' => 'Vui lòng nhập mật khẩu mới.',
            'Mat_khau.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'Mat_khau.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'Mat_khau.regex' => 'Mật khẩu mới phải có cả chữ và số.',
        ]);

        $status = Password::reset(
            [
                'Email' => $request->Email,
                'password' => $request->Mat_khau,
                'password_confirmation' => $request->Mat_khau_confirmation,
                'token' => $request->token,
            ],
            function ($user, $password) {
                $user->Mat_khau = Hash::make($password);
                $user->Trang_thai = 'Hoạt động'; // Tự động mở khóa nếu đang bị khóa
                $user->So_lan_sai_mat_khau = 0;
                $user->Phai_doi_mat_khau = false;
                $user->save();

                Auth::login($user);
                ActivityLogger::log('Sửa', 'Người dùng', "Đặt lại mật khẩu thành công qua email", $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->redirectByRole(Auth::user())->with('success', 'Đặt lại mật khẩu thành công!');
        }

        return back()->withErrors(['Email' => 'Đường dẫn đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
    }

    /**
     * Điều hướng về trang chủ theo vai trò người dùng (06-luong-man-hinh.md mục 2)
     */
    public function redirectByRole($user)
    {
        return match ($user->Ma_vai_tro) {
            'QUAN_TRI' => redirect()->route('quantri.trang-chu'),
            'QL_KHO' => redirect()->route('kho.trang-chu'),
            'NV_KHO' => redirect()->route('nv-kho.trang-chu'),
            'KE_TOAN' => redirect()->route('ke-toan.trang-chu'),
            'KINH_DOANH' => redirect()->route('kinh-doanh.trang-chu'),
            default => redirect()->route('dashboard'),
        };
    }
}
