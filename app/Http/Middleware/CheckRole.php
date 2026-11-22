<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Kiểm tra bắt buộc đổi mật khẩu lần đầu
        if ($user->Phai_doi_mat_khau && !$request->is('doi-mat-khau-lan-dau*') && !$request->is('dang-xuat')) {
            return redirect()->route('first-login.change-password')
                ->with('warning', 'Bạn bắt buộc phải đổi mật khẩu lần đầu trước khi sử dụng hệ thống.');
        }

        // Kiểm tra vai trò
        if (!empty($roles) && !in_array($user->Ma_vai_tro, $roles)) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}

