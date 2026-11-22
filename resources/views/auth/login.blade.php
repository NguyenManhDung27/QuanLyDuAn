<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập – Soul Wine</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      margin: 0;
      background: #FAF5F3;
      font-family: 'Be Vietnam Pro', sans-serif;
      color: #241417;
    }
    a { color: #8B1E2D; text-decoration: none; }
    a:hover { color: #5E1320; text-decoration: underline; }
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
      background-color: #2A0A10;
      background-image: linear-gradient(rgba(30, 6, 10, 0.65), rgba(30, 6, 10, 0.65)), url('{{ asset("images/login-bg.jpg") }}');
      background-size: cover;
      background-position: center;
    }
    .login-card {
      width: 100%;
      max-width: 440px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      padding: 36px 32px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.98);
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
    }
    .logo-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
    }
    .logo-img {
      width: 152px;
      height: auto;
      display: block;
    }
    .title-box {
      display: flex;
      flex-direction: column;
      gap: 6px;
      text-align: center;
    }
    .title-box h1 {
      font-size: 22px;
      font-weight: 700;
      color: #241417;
    }
    .title-box p {
      font-size: 14px;
      color: #6B5559;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      font-size: 13px;
      font-weight: 500;
    }
    .form-control {
      height: 44px;
      padding: 0 12px;
      border-radius: 8px;
      border: 1px solid #DCCAC6;
      font: inherit;
      font-size: 14px;
      color: #241417;
      background: #FFFFFF;
      outline: none;
      transition: border-color 0.15s ease;
    }
    .form-control:focus {
      border-color: #8B1E2D;
      box-shadow: 0 0 0 3px rgba(139, 30, 45, 0.12);
    }
    .form-control.is-invalid {
      border-color: #D32F2F;
    }
    .btn-submit {
      height: 48px;
      border-radius: 8px;
      border: 0;
      background: #8B1E2D;
      color: #FFFFFF;
      font: inherit;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .btn-submit:hover {
      background: #6E1423;
    }
    .alert-error {
      padding: 10px 14px;
      border-radius: 8px;
      background: #FDE8E8;
      border: 1px solid #F8B4B4;
      color: #9B1C1C;
      font-size: 13px;
      line-height: 1.4;
    }
    .alert-success {
      padding: 10px 14px;
      border-radius: 8px;
      background: #DEF7EC;
      border: 1px solid #BCF0DA;
      color: #03543F;
      font-size: 13px;
    }
    .note-text {
      font-size: 12px;
      color: #6B5559;
      text-align: center;
      line-height: 1.4;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="logo-box">
        <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Soul Wine – Fine wine for fine life">
      </div>

      <div class="title-box">
        <h1>Đăng nhập</h1>
        <p>Hệ thống quản lý kho Soul Wine</p>
      </div>

      @if(session('success'))
        <div class="alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $error)
            <div>• {{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('login.post') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf
        <div class="form-group">
          <label for="Ten_dang_nhap">Tên đăng nhập</label>
          <input 
            type="text" 
            id="Ten_dang_nhap" 
            name="Ten_dang_nhap" 
            value="{{ old('Ten_dang_nhap') }}" 
            placeholder="Tên đăng nhập" 
            class="form-control @error('Ten_dang_nhap') is-invalid @enderror" 
            autocomplete="username" 
            required 
            autofocus
          >
        </div>

        <div class="form-group">
          <label for="Mat_khau">Mật khẩu</label>
          <input 
            type="password" 
            id="Mat_khau" 
            name="Mat_khau" 
            placeholder="Mật khẩu" 
            class="form-control @error('Mat_khau') is-invalid @enderror" 
            autocomplete="current-password" 
            required
          >
        </div>

        <div style="display: flex; justify-content: flex-end;">
          <a href="{{ route('password.request') }}" style="font-size: 14px; font-weight: 500;">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn-submit">Đăng nhập</button>

        <p class="note-text">
          Nhập sai mật khẩu 5 lần liên tiếp, tài khoản sẽ bị khóa.
        </p>
      </form>
    </div>
  </div>
</body>
</html>

