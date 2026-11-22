<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đặt lại mật khẩu – Soul Wine</title>
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
    .container {
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
    .card {
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
    }
    .form-control:focus {
      border-color: #8B1E2D;
      box-shadow: 0 0 0 3px rgba(139, 30, 45, 0.12);
    }
    .password-rules {
      margin: 0;
      padding: 12px 16px 12px 32px;
      border-radius: 8px;
      background: #F6ECEA;
      font-size: 13px;
      color: #3B2A2D;
      display: flex;
      flex-direction: column;
      gap: 4px;
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
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="logo-box">
        <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Soul Wine – Fine wine for fine life">
      </div>

      <div class="title-box">
        <h1>Đặt lại mật khẩu</h1>
        <p>Email: <b style="color: #241417;">{{ $email ?? old('Email') }}</b></p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $error)
            <div>• {{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('password.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="Email" value="{{ $email ?? old('Email') }}">

        <div class="form-group">
          <label for="Mat_khau">Mật khẩu mới</label>
          <input type="password" id="Mat_khau" name="Mat_khau" placeholder="Nhập mật khẩu mới" class="form-control" required autofocus>
        </div>

        <div class="form-group">
          <label for="Mat_khau_confirmation">Nhập lại mật khẩu mới</label>
          <input type="password" id="Mat_khau_confirmation" name="Mat_khau_confirmation" placeholder="Nhập lại mật khẩu mới" class="form-control" required>
        </div>

        <ul class="password-rules">
          <li>Ít nhất 8 ký tự</li>
          <li>Có cả chữ và số</li>
        </ul>

        <button type="submit" class="btn-submit">Đặt lại mật khẩu</button>

        <p style="margin: 0; font-size: 13px; color: #6B5559; text-align: center; line-height: 1.4;">
          Sau khi đặt lại, bạn đăng nhập bằng mật khẩu mới. Nếu tài khoản đang bị khóa do sai mật khẩu, tài khoản sẽ được mở khóa.
        </p>
      </form>
    </div>
  </div>
</body>
</html>

