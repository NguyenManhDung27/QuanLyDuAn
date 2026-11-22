@extends('layouts.app')

@section('title', 'Trang chủ – Soul Wine')

@section('content')
  @php
    $user = auth()->user();
    $role = $user->Ma_vai_tro ?? '';
    $roleName = $user->vaiTro->Ten_vai_tro ?? $role;
  @endphp

  <div class="breadcrumb-box">
    <span class="breadcrumb-path">Tổng quan / Trang chủ</span>
    <h1 class="page-title">Xin chào, {{ $user->Ho_ten ?? $user->Ten_dang_nhap }}! 👋</h1>
    <p class="page-desc">Bạn đang đăng nhập với vai trò: <strong style="color: #8B1E2D;">{{ $roleName }}</strong></p>
  </div>

  {{-- WIDGETS TỔNG QUAN THEO VAI TRÒ --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
    
    @if(in_array($role, ['THU_KHO', 'ADMIN', 'QUAN_TRI']))
      <div class="card-box" style="border-left: 4px solid #8B1E2D;">
        <span style="font-size: 13px; color: #6B5559;">Phiếu nhập chờ duyệt</span>
        <div style="font-size: 32px; font-weight: 700; color: #8B1E2D; margin-top: 6px;">4</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #28a745;">Đơn đặt hàng từ NCC về kho</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #D97706;">
        <span style="font-size: 13px; color: #6B5559;">Phiếu xuất chờ phân bổ lô</span>
        <div style="font-size: 32px; font-weight: 700; color: #D97706; margin-top: 6px;">8</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Cần chỉ định lô trước khi xuất</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #DC2626;">
        <span style="font-size: 13px; color: #6B5559;">Cảnh báo tồn kho tối thiểu</span>
        <div style="font-size: 32px; font-weight: 700; color: #DC2626; margin-top: 6px;">3 loại rượu</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #DC2626;">Sắp hết hàng trong kho</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #2563EB;">
        <span style="font-size: 13px; color: #6B5559;">Đang kiểm kê</span>
        <div style="font-size: 32px; font-weight: 700; color: #2563EB; margin-top: 6px;">1 đợt</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Đợt kiểm kê kho tháng 11</p>
      </div>
    @endif

    @if($role == 'KE_TOAN')
      <div class="card-box" style="border-left: 4px solid #8B1E2D;">
        <span style="font-size: 13px; color: #6B5559;">Tổng giá trị tồn kho</span>
        <div style="font-size: 28px; font-weight: 700; color: #8B1E2D; margin-top: 6px;">2.450.000.000 đ</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #28a745;">Tính theo giá vốn bình quân</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #D97706;">
        <span style="font-size: 13px; color: #6B5559;">Đơn đặt hàng chờ duyệt giá</span>
        <div style="font-size: 32px; font-weight: 700; color: #D97706; margin-top: 6px;">2 đơn</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Kiểm tra công nợ nhà cung cấp</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #2563EB;">
        <span style="font-size: 13px; color: #6B5559;">Phiếu kiểm kê chờ duyệt lệch</span>
        <div style="font-size: 32px; font-weight: 700; color: #2563EB; margin-top: 6px;">1 phiếu</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Điều chỉnh giá vốn chênh lệch</p>
      </div>
    @endif

    @if($role == 'NV_KHO')
      <div class="card-box" style="border-left: 4px solid #8B1E2D;">
        <span style="font-size: 13px; color: #6B5559;">Hàng cần kiểm đếm nhập</span>
        <div style="font-size: 32px; font-weight: 700; color: #8B1E2D; margin-top: 6px;">3 phiếu</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #28a745;">Xe giao hàng NCC đã tới</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #D97706;">
        <span style="font-size: 13px; color: #6B5559;">Đơn cần soạn hàng xuất</span>
        <div style="font-size: 32px; font-weight: 700; color: #D97706; margin-top: 6px;">7 đơn</span></div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Soạn theo mã lô phân bổ</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #2563EB;">
        <span style="font-size: 13px; color: #6B5559;">Vị trí cần đếm kiểm kê</span>
        <div style="font-size: 32px; font-weight: 700; color: #2563EB; margin-top: 6px;">Kệ A1 - A4</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Nhập số đếm thực tế</p>
      </div>
    @endif

    @if($role == 'NV_BAN_HANG')
      <div class="card-box" style="border-left: 4px solid #8B1E2D;">
        <span style="font-size: 13px; color: #6B5559;">Đơn bán trong ngày</span>
        <div style="font-size: 32px; font-weight: 700; color: #8B1E2D; margin-top: 6px;">12 đơn</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #28a745;">Doanh thu: 148.000.000 đ</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #D97706;">
        <span style="font-size: 13px; color: #6B5559;">Đơn đang chuẩn bị hàng</span>
        <div style="font-size: 32px; font-weight: 700; color: #D97706; margin-top: 6px;">5 đơn</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Kho đang soạn hàng</p>
      </div>

      <div class="card-box" style="border-left: 4px solid #2563EB;">
        <span style="font-size: 13px; color: #6B5559;">Đang giao hàng (vận đơn)</span>
        <div style="font-size: 32px; font-weight: 700; color: #2563EB; margin-top: 6px;">7 đơn</div>
        <p style="margin: 6px 0 0; font-size: 13px; color: #6B5559;">Đơn vị vận chuyển đang phát</p>
      </div>
    @endif

  </div>

  {{-- KHỐI HƯỚNG DẪN TEST ROLE --}}
  <div class="card-box" style="margin-top: 10px;">
    <h3 style="margin: 0 0 12px; font-size: 16px; color: #6E1423;">💡 Hướng dẫn kiểm tra phân quyền Menu 5 Role:</h3>
    <p style="margin: 0 0 10px; font-size: 14px; color: #6B5559;">
      Thanh menu bên trái đã được lập trình động dựa trên vai trò của tài khoản hiện tại. Bạn có thể bấm <strong>Đăng xuất</strong> ở góc phải trên cùng và đăng nhập lại bằng các tài khoản sau để thấy sự khác biệt về menu:
    </p>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="background: #F6ECEA; text-align: left;">
            <th style="padding: 10px 14px; border-bottom: 2px solid #EADFDC;">Vai trò</th>
            <th style="padding: 10px 14px; border-bottom: 2px solid #EADFDC;">Tên đăng nhập</th>
            <th style="padding: 10px 14px; border-bottom: 2px solid #EADFDC;">Mật khẩu</th>
            <th style="padding: 10px 14px; border-bottom: 2px solid #EADFDC;">Menu đặc trưng</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid #EADFDC;">
            <td style="padding: 10px 14px;"><strong>Quản trị hệ thống</strong></td>
            <td style="padding: 10px 14px;"><code>admin</code></td>
            <td style="padding: 10px 14px;"><code>12345678</code></td>
            <td style="padding: 10px 14px;">Quản trị tài khoản, Phân quyền, Kho hàng, Audit log</td>
          </tr>
          <tr style="border-bottom: 1px solid #EADFDC;">
            <td style="padding: 10px 14px;"><strong>Thủ kho</strong></td>
            <td style="padding: 10px 14px;"><code>thukho</code></td>
            <td style="padding: 10px 14px;"><code>12345678</code></td>
            <td style="padding: 10px 14px;">Mua & Nhập, Xuất kho, Kiểm kê, Tồn kho, Báo cáo tồn</td>
          </tr>
          <tr style="border-bottom: 1px solid #EADFDC;">
            <td style="padding: 10px 14px;"><strong>Kế toán</strong></td>
            <td style="padding: 10px 14px;"><code>ketoan</code></td>
            <td style="padding: 10px 14px;"><code>12345678</code></td>
            <td style="padding: 10px 14px;">Đơn đặt NCC, Công nợ, Doanh thu, Duyệt chênh lệch kiểm kê</td>
          </tr>
          <tr style="border-bottom: 1px solid #EADFDC;">
            <td style="padding: 10px 14px;"><strong>Nhân viên kho</strong></td>
            <td style="padding: 10px 14px;"><code>nvkho</code></td>
            <td style="padding: 10px 14px;"><code>12345678</code></td>
            <td style="padding: 10px 14px;">Kiểm đếm nhập, Soạn hàng xuất, Đếm kiểm kê thực tế</td>
          </tr>
          <tr style="border-bottom: 1px solid #EADFDC;">
            <td style="padding: 10px 14px;"><strong>Nhân viên bán hàng</strong></td>
            <td style="padding: 10px 14px;"><code>nvbanhang</code></td>
            <td style="padding: 10px 14px;"><code>12345678</code></td>
            <td style="padding: 10px 14px;">Tạo & theo dõi đơn bán, Khách hàng, Tra cứu tồn bán</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection

