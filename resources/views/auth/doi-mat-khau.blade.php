@extends('layouts.app')

@section('title', 'Đổi mật khẩu – Soul Wine')

@section('content')
  <div class="breadcrumb-box">
    <span class="breadcrumb-path">Tài khoản / Đổi mật khẩu</span>
    <h1 class="page-title">Đổi mật khẩu</h1>
    <p class="page-desc">Mở từ nút Đổi mật khẩu trên header. Màn này giống nhau cho mọi vai trò.</p>
  </div>

  @if($errors->any())
    <div class="alert-danger">
      @foreach($errors->all() as $error)
        <div>• {{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form action="{{ route('password.change.post') }}" method="POST" class="card-box" style="max-width: 480px; display: flex; flex-direction: column; gap: 16px;">
    @csrf
    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
      <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
      <input 
        type="password" 
        id="mat_khau_hien_tai" 
        name="mat_khau_hien_tai" 
        placeholder="Nhập mật khẩu hiện tại" 
        style="height: 44px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; outline: none;" 
        required
      >
    </div>

    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
      <label for="mat_khau_moi">Mật khẩu mới</label>
      <input 
        type="password" 
        id="mat_khau_moi" 
        name="mat_khau_moi" 
        placeholder="Nhập mật khẩu mới" 
        style="height: 44px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; outline: none;" 
        required
      >
    </div>

    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
      <label for="mat_khau_moi_confirmation">Nhập lại mật khẩu mới</label>
      <input 
        type="password" 
        id="mat_khau_moi_confirmation" 
        name="mat_khau_moi_confirmation" 
        placeholder="Nhập lại mật khẩu mới" 
        style="height: 44px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; outline: none;" 
        required
      >
    </div>

    <ul style="margin: 0; padding: 12px 16px 12px 32px; border-radius: 8px; background: #F6ECEA; font-size: 13px; color: #3B2A2D; display: flex; flex-direction: column; gap: 4px;">
      <li>Ít nhất 8 ký tự</li>
      <li>Có cả chữ và số</li>
      <li>Khác mật khẩu hiện tại</li>
    </ul>

    <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px;">
      <a href="{{ route('dashboard') }}" style="height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">Hủy bỏ</a>
      <button type="submit" style="height: 44px; padding: 0 24px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Lưu</button>
    </div>

    <p style="margin: 0; font-size: 13px; color: #6B5559;">
      Đổi thành công thì giữ phiên hiện tại, các phiên đăng nhập trên thiết bị khác bị đăng xuất.
    </p>
  </form>
@endsection

