@extends('layouts.app')

@section('title', 'Phiếu xuất kho – Soul Wine')

@section('content')
<div style="padding: 24px 32px; max-width: 1300px; margin: 0 auto;">

  {{-- Header --}}
  <div style="margin-bottom: 24px;">
    <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
      <span>Xuất kho</span> &nbsp;/&nbsp; <strong style="color: #241417;">Phiếu xuất kho</strong>
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0 0 6px;">Phiếu xuất kho</h1>
    <p style="margin: 0; font-size: 14px; color: #6B5559;">Phiếu xuất tự sinh khi Kinh doanh xác nhận đơn bán; lô được phân bổ sẵn theo FIFO.</p>
  </div>

  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px; margin-bottom: 16px;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">{{ session('error') }}</div>
  @endif

  {{-- Tabs --}}
  @php
    $tabList = [
      'all'                => 'Tất cả',
      'Đang chuẩn bị hàng' => 'Đang chuẩn bị hàng',
      'Đang giao'          => 'Đang giao',
      'Giao thành công'    => 'Giao thành công',
      'Giao hàng thất bại' => 'Giao hàng thất bại',
      'Đã hủy'             => 'Đã hủy',
    ];
  @endphp
  <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
    @foreach($tabList as $key => $label)
    @php
      $isActive = ($tab === $key);
      $count = $counts[$key] ?? 0;
    @endphp
    <a href="{{ route('nv-kho.phieu-xuat.index', array_merge(request()->query(), ['tab' => $key, 'page' => 1])) }}"
      style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 999px; font-size: 13px; font-weight: {{ $isActive ? '700' : '500' }}; text-decoration: none; transition: all 0.15s ease; {{ $isActive ? 'background: #6E1423; color: #FFFFFF; border: 0;' : 'background: #FFFFFF; color: #241417; border: 1px solid #DCCAC6;' }}">
      <span>{{ $label }}</span>
      <span style="opacity: 0.75; font-size: 12px;">({{ $count }})</span>
    </a>
    @endforeach
  </div>

  {{-- Bộ lọc --}}
  <form method="GET" action="{{ route('nv-kho.phieu-xuat.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; background: #FDFAF8; border: 1px solid #EDE0DC; border-radius: 10px; padding: 16px 20px;">
    <input type="hidden" name="tab" value="{{ $tab }}">

    <div style="display: flex; flex-direction: column; gap: 4px; flex: 2; min-width: 220px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Tìm kiếm</label>
      <input type="text" name="search" value="{{ $search }}" placeholder="Mã phiếu, mã đơn bán, khách hàng, mã vận đơn..."
        style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff; outline: none;">
    </div>

    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 140px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Từ ngày</label>
      <input type="date" name="tu_ngay" value="{{ $tuNgay }}"
        style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff;">
    </div>

    <div style="display: flex; flex-direction: column; gap: 4px; min-width: 140px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Đến ngày</label>
      <input type="date" name="den_ngay" value="{{ $denNgay }}"
        style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff;">
    </div>

    <button type="submit" style="height: 36px; padding: 0 20px; border-radius: 6px; border: 0; background: #8B1E2D; color: #fff; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('nv-kho.phieu-xuat.index', ['tab' => $tab]) }}" style="height: 36px; padding: 0 16px; border-radius: 6px; border: 1px solid #D6C8C5; background: #fff; color: #6B5559; font: inherit; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center;">Đặt lại</a>
  </form>

  {{-- Bảng danh sách --}}
  <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px; min-width: 980px;">
      <thead>
        <tr style="background: #F8F0EE;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left; white-space: nowrap;">Mã phiếu</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Đơn bán</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Người nhận</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Địa chỉ giao</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Ngày tạo</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Vận đơn</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($phieuXuats as $px)
        @php
          $db = $px->donHangBan;
          $badgeStyle = match($px->Trang_thai_giao_hang) {
            'Đang chuẩn bị hàng' => 'background:#FCEFD9;color:#8A4B08;',
            'Đang giao'          => 'background:#E3EAFB;color:#1D3F9E;',
            'Giao thành công'    => 'background:#E5F2EA;color:#1E5B36;',
            'Giao hàng thất bại' => 'background:#F6E1E1;color:#8B1E2D;',
            'Đã hủy'             => 'background:#EEEAE9;color:#6B5559;',
            default              => 'background:#F3F4F6;color:#374151;',
          };
          $isThieu = $thieu[$px->Ma_phieu_xuat] ?? false;
          $isPendingHoan = ($px->Trang_thai_hoan_hang === 'Chờ xử lý');
        @endphp
        <tr style="border-top: 1px solid #F3EAE7;" onmouseover="this.style.background='#FDF8F7';" onmouseout="this.style.background='';">
          <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; white-space: nowrap;">
            {{ $px->Ma_phieu_xuat }}
          </td>
          <td style="padding: 12px 16px; font-weight: 600; color: #241417; white-space: nowrap;">
            {{ $px->Ma_don_hang_ban }}
          </td>
          <td style="padding: 12px 16px; color: #241417;">
            {{ $db?->Ten_nguoi_nhan ?? '—' }}<br>
            <span style="font-size: 12px; color: #6B5559;">{{ $db?->Sdt_nguoi_nhan }}</span>
          </td>
          <td style="padding: 12px 16px; color: #3B2A2D; font-size: 13px; max-width: 250px;">
            {{ $db?->Dia_chi_giao_hang ?? '—' }}
          </td>
          <td style="padding: 12px 16px; color: #6B5559; white-space: nowrap;">
            {{ $px->Ngay_tao ? date('d/m/Y', strtotime($px->Ngay_tao)) : '—' }}
          </td>
          <td style="padding: 12px 16px; color: #241417; font-size: 13px;">
            @if($px->Don_vi_van_chuyen || $px->Ma_van_don)
              <strong>{{ $px->Don_vi_van_chuyen }}</strong><br>
              <span style="color: #6B5559;">{{ $px->Ma_van_don ?? '—' }}</span>
            @else
              <span style="color: #9B7570;">—</span>
            @endif
          </td>
          <td style="padding: 12px 16px; text-align: center;">
            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
              <span style="display: inline-block; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; white-space: nowrap; {{ $badgeStyle }}">{{ $px->Trang_thai_giao_hang }}</span>
              @if($isThieu)
                <span title="Tồn thực tế không đủ cho số lượng cần xuất" style="display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #FBEDED; color: #B42318; white-space: nowrap;">⚠ Thiếu tồn</span>
              @endif
              @if($isPendingHoan)
                <span style="display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; border: 1px solid #B42318; color: #B42318; white-space: nowrap;">Chờ xử lý hoàn</span>
              @endif
            </div>
          </td>
          <td style="padding: 12px 16px; text-align: center;">
            <a href="{{ route('nv-kho.phieu-xuat.show', $px->Ma_phieu_xuat) }}" style="display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600; color: #8B1E2D; text-decoration: none;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              Xem
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="padding: 40px; text-align: center; color: #6B5559;">Không có phiếu xuất kho nào.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($phieuXuats->hasPages())
  <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
    {{ $phieuXuats->links() }}
  </div>
  @endif

</div>
@endsection

