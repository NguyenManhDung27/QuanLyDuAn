@extends('layouts.app')

@section('title', 'Nhật ký hệ thống – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div>
    <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
      <span>Quản trị hệ thống</span> &nbsp;/&nbsp; <strong style="color: #241417;">Nhật ký hệ thống</strong>
    </div>
    <h1 style="font-size: 24px; font-weight: 700; color: #241417; margin-bottom: 4px;">Nhật ký hệ thống</h1>
    <p style="font-size: 14px; color: #6B5559;">Ghi lại người thực hiện, thời điểm và thao tác trên các chứng từ. Chỉ xem, không sửa hoặc xóa được.</p>
  </div>

  {{-- Bộ lọc (Filters) --}}
  <form method="GET" action="{{ route('quantri.nhat-ky.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC;">
    {{-- Từ ngày --}}
    <div style="flex: 1 1 140px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Từ ngày</label>
      <input type="date" name="tu_ngay" value="{{ request('tu_ngay') }}" style="height: 40px; padding: 0 10px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 13px; background: #FFFFFF;">
    </div>

    {{-- Đến ngày --}}
    <div style="flex: 1 1 140px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Đến ngày</label>
      <input type="date" name="den_ngay" value="{{ request('den_ngay') }}" style="height: 40px; padding: 0 10px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 13px; background: #FFFFFF;">
    </div>

    {{-- Người thực hiện --}}
    <div style="flex: 1.5 1 180px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Người thực hiện</label>
      <input type="text" name="nguoi_thuc_hien" value="{{ request('nguoi_thuc_hien') }}" placeholder="Họ tên hoặc tên đăng nhập" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 13px;">
    </div>

    {{-- Loại chứng từ --}}
    <div style="flex: 1 1 150px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Loại chứng từ</label>
      <select name="loai_chung_tu" style="height: 40px; padding: 0 10px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 13px; background: #FFFFFF;">
        <option value="">Tất cả</option>
        <option value="Đơn đặt hàng" {{ request('loai_chung_tu') == 'Đơn đặt hàng' ? 'selected' : '' }}>Đơn đặt hàng</option>
        <option value="Phiếu nhập kho" {{ request('loai_chung_tu') == 'Phiếu nhập kho' ? 'selected' : '' }}>Phiếu nhập kho</option>
        <option value="Đơn hàng bán" {{ request('loai_chung_tu') == 'Đơn hàng bán' ? 'selected' : '' }}>Đơn hàng bán</option>
        <option value="Phiếu xuất kho" {{ request('loai_chung_tu') == 'Phiếu xuất kho' ? 'selected' : '' }}>Phiếu xuất kho</option>
        <option value="Phiếu kiểm kê" {{ request('loai_chung_tu') == 'Phiếu kiểm kê' ? 'selected' : '' }}>Phiếu kiểm kê</option>
        <option value="Danh mục" {{ request('loai_chung_tu') == 'Danh mục' ? 'selected' : '' }}>Danh mục</option>
        <option value="Người dùng" {{ request('loai_chung_tu') == 'Người dùng' ? 'selected' : '' }}>Người dùng</option>
        <option value="Kho" {{ request('loai_chung_tu') == 'Kho' ? 'selected' : '' }}>Kho</option>
      </select>
    </div>

    {{-- Thao tác --}}
    <div style="flex: 1 1 140px; display: flex; flex-direction: column; gap: 6px;">
      <label style="font-size: 12px; font-weight: 600; color: #6B5559;">Thao tác</label>
      <select name="thao_tac" style="height: 40px; padding: 0 10px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 13px; background: #FFFFFF;">
        <option value="">Tất cả</option>
        <option value="Tạo" {{ request('thao_tac') == 'Tạo' ? 'selected' : '' }}>Tạo</option>
        <option value="Sửa" {{ request('thao_tac') == 'Sửa' ? 'selected' : '' }}>Sửa</option>
        <option value="Duyệt" {{ request('thao_tac') == 'Duyệt' ? 'selected' : '' }}>Duyệt</option>
        <option value="Từ chối" {{ request('thao_tac') == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
        <option value="Hủy" {{ request('thao_tac') == 'Hủy' ? 'selected' : '' }}>Hủy</option>
        <option value="Xác nhận" {{ request('thao_tac') == 'Xác nhận' ? 'selected' : '' }}>Xác nhận</option>
        <option value="Đổi lô" {{ request('thao_tac') == 'Đổi lô' ? 'selected' : '' }}>Đổi lô</option>
        <option value="Khóa / Mở khóa" {{ request('thao_tac') == 'Khóa / Mở khóa' ? 'selected' : '' }}>Khóa / Mở khóa</option>
        <option value="Đăng nhập" {{ request('thao_tac') == 'Đăng nhập' ? 'selected' : '' }}>Đăng nhập</option>
        <option value="Đăng xuất" {{ request('thao_tac') == 'Đăng xuất' ? 'selected' : '' }}>Đăng xuất</option>
      </select>
    </div>

    {{-- Nút lọc & Đặt lại --}}
    <div>
      <button type="submit" style="height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #8B1E2D; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
        Lọc
      </button>
      @if(request()->hasAny(['tu_ngay', 'den_ngay', 'nguoi_thuc_hien', 'loai_chung_tu', 'thao_tac']))
        <a href="{{ route('quantri.nhat-ky.index') }}" style="display: inline-flex; align-items: center; height: 40px; padding: 0 14px; margin-left: 8px; border-radius: 8px; border: 1px solid #EADFDC; color: #6B5559; font-size: 13px; text-decoration: none;">
          Đặt lại
        </a>
      @endif
    </div>
  </form>

  {{-- Bảng Nhật ký hệ thống --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
        <thead>
          <tr style="background: #FAF5F3; color: #6B5559; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 18px; font-weight: 600;">Thời điểm</th>
            <th style="padding: 12px 18px; font-weight: 600;">Người thực hiện</th>
            <th style="padding: 12px 18px; font-weight: 600;">Vai trò</th>
            <th style="padding: 12px 18px; font-weight: 600;">Thao tác</th>
            <th style="padding: 12px 18px; font-weight: 600;">Chứng từ / Loại</th>
            <th style="padding: 12px 18px; font-weight: 600;">Nội dung chi tiết</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr style="border-bottom: 1px solid #F3EAE8; transition: background 0.1s ease;" onmouseover="this.style.background='#FAF7F6';" onmouseout="this.style.background='transparent';">
              <td style="padding: 12px 18px; color: #6B5559; white-space: nowrap; font-family: monospace;">
                {{ $log->created_at->format('d/m/Y H:i:s') }}
              </td>
              <td style="padding: 12px 18px; font-weight: 600; color: #241417;">
                {{ $log->causer ? $log->causer->Ho_ten : ($log->properties['user_name'] ?? 'Hệ thống') }}
              </td>
              <td style="padding: 12px 18px; color: #6B5559;">
                {{ $log->causer && $log->causer->vaiTro ? $log->causer->vaiTro->Ten_vai_tro : ($log->properties['user_role'] ?? '—') }}
              </td>
              <td style="padding: 12px 18px;">
                @php
                  $event = $log->event ?? ($log->properties['action'] ?? 'Thao tác');
                  $color = match($event) {
                    'Tạo' => '#0E9F6E',
                    'Sửa' => '#3F83F8',
                    'Duyệt' => '#046C4E',
                    'Từ chối' => '#C81E1E',
                    'Hủy' => '#E02424',
                    'Xác nhận' => '#1A56DB',
                    'Đổi lô' => '#9061F9',
                    'Khóa' => '#E02424',
                    'Khóa / Mở khóa' => '#D97706',
                    'Đăng nhập' => '#057A55',
                    'Đăng xuất' => '#76A9FA',
                    default => '#4B5563'
                  };
                @endphp
                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: {{ $color }}15; color: {{ $color }};">
                  {{ $event }}
                </span>
              </td>
              <td style="padding: 12px 18px; color: #4B5563; font-weight: 500;">
                {{ $log->log_name ?? '—' }}
                @if($log->subject_id)
                  <span style="font-family: monospace; font-size: 12px; color: #8B1E2D;">({{ $log->subject_id }})</span>
                @endif
              </td>
              <td style="padding: 12px 18px; color: #241417;">
                {{ $log->description }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="padding: 32px 18px; text-align: center; color: #6B5559;">
                Không tìm thấy nhật ký thao tác nào phù hợp với bộ lọc.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer: Tổng số & Phân trang --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; border-top: 1px solid #EADFDC; background: #FFFFFF; font-size: 13px; color: #6B5559;">
      <div>
        Hiển thị <strong>{{ $logs->firstItem() ?? 0 }}</strong> – <strong>{{ $logs->lastItem() ?? 0 }}</strong> trên tổng số <strong>{{ $logs->total() }}</strong> nhật ký
      </div>
      <div>
        {{ $logs->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

