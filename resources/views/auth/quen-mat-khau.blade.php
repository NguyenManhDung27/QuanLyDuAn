<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quên mật khẩu – Soul Wine</title>
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
      line-height: 1.4;
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
    .alert-success {
      padding: 14px;
      border-radius: 8px;
      background: #E5F2EA;
      border: 1px solid #B4DFCA;
      color: #1E5B36;
      font-size: 14px;
      text-align: center;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="logo-box">
        <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="Soul Wine – Fine wine for fine life">
      </div>

      @if (session('status'))
        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center;">
          <div style="width: 56px; height: 56px; border-radius: 999px; background: #E5F2EA; display: flex; align-items: center; justify-content: center;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1E5B36" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
          </div>
          <h1 style="margin: 0; font-size: 22px; font-weight: 700;">Đã gửi đường dẫn</h1>
          <p style="margin: 0; font-size: 14px; color: #6B5559;">Đường dẫn đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.</p>
          <a href="{{ route('login') }}" style="margin-top: 10px; font-size: 14px;">← Quay lại đăng nhập</a>
        </div>
      @else
        <div class="title-box">
          <h1>Quên mật khẩu</h1>
          <p>Nhập email của tài khoản. Hệ thống sẽ gửi đường dẫn đặt lại mật khẩu vào email này.</p>
        </div>

        @if($errors->any())
          <div class="alert-error">
            @foreach($errors->all() as $error)
              <div>• {{ $error }}</div>
            @endforeach
          </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
          @csrf
          <div class="form-group">
            <label for="Email">Email</label>
            <input type="email" id="Email" name="Email" value="{{ old('Email') }}" placeholder="ten@soulwine.vn" class="form-control" required autofocus>
          </div>

          <button type="submit" class="btn-submit">Gửi đường dẫn</button>

          <div style="text-align: center;">
            <a href="{{ route('login') }}" style="font-size: 14px;">← Quay lại đăng nhập</a>
          </div>
        </form>
      @endif
    </div>
  </div>
</body>
</html>

