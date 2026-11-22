@extends('layouts.app')

@section('title', 'Đơn hàng bán – Soul Wine')

@section('content')
<div style="padding: 24px 32px; max-width: 1300px; margin: 0 auto;">

  {{-- Header --}}
  <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Bán hàng</span> &nbsp;/&nbsp; <strong style="color: #241417;">Đơn hàng bán</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Đơn hàng bán</h1>
    </div>
    <a href="{{ route('kinh-doanh.don-ban.create') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); white-space: nowrap;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>+ Lập đơn bán mới</span>
    </a>
  </div>

  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px; margin-bottom: 16px;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">{{ session('error') }}</div>
  @endif

  {{-- Bộ lọc --}}
  <form method="GET" action="{{ route('kinh-doanh.don-ban.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; background: #FDFAF8; border: 1px solid #EDE0DC; border-radius: 10px; padding: 16px 20px;">
    <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 200px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Tìm kiếm</label>
      <input type="text" name="search" value="{{ $search }}" placeholder="Mã đơn, tên hoặc khách hàng..."
        style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff; outline: none;"
        onfocus="this.style.borderColor='#8B1E2D';" onblur="this.style.borderColor='#D6C8C5';">
    </div>

    <div style="display: flex; flex-direction: column; gap: 4px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Kho xuất</label>
      <select name="ma_kho" style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff;">
        <option value="">Tất cả kho</option>
        @foreach($khos as $k)
          <option value="{{ $k->Ma_kho }}" @selected($maKho === $k->Ma_kho)>{{ $k->Ten_kho }}</option>
        @endforeach
      </select>
    </div>

    <div style="display: flex; flex-direction: column; gap: 4px;">
      <label style="font-size: 12px; color: #6B5559; font-weight: 600;">Trạng thái</label>
      <select name="trang_thai" style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff;">
        <option value="">Tất cả</option>
        @foreach(['Đang chuẩn bị hàng','Đang giao','Hoàn thành','Giao thất bại','Đã hủy'] as $tt)
          <option value="{{ $tt }}" @selected($trangThai === $tt)>{{ $tt }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" style="height: 36px; padding: 0 20px; border-radius: 6px; border: 0; background: #8B1E2D; color: #fff; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('kinh-doanh.don-ban.index') }}" style="height: 36px; padding: 0 16px; border-radius: 6px; border: 1px solid #D6C8C5; background: #fff; color: #6B5559; font: inherit; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center;">Đặt lại</a>
  </form>

  {{-- Bảng danh sách --}}
  <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F8F0EE;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left; white-space: nowrap;">Mã đơn</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Kho xuất</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Khách hàng</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Người nhận</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Tiền hàng</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: left;">Ngày xác nhận</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($donBans as $db)
        @php
          $badgeStyle = match($db->Trang_thai) {
            'Đang chuẩn bị hàng' => 'background:#EEF2FF;color:#3730A3;',
            'Đang giao'           => 'background:#FEF3C7;color:#92400E;',
            'Hoàn thành'          => 'background:#DEF7EC;color:#03543F;',
            'Giao thất bại'       => 'background:#FDE8E8;color:#9B1C1C;',
            'Đã hủy'              => 'background:#F3F4F6;color:#6B7280;',
            default               => 'background:#F3F4F6;color:#374151;',
          };
          $isThieu = $thieu[$db->Ma_don_hang_ban] ?? false;
        @endphp
        <tr style="border-top: 1px solid #F3EAE7;" onmouseover="this.style.background='#FDF8F7';" onmouseout="this.style.background='';">
          <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D;">
            {{ $db->Ma_don_hang_ban }}
            @if($isThieu)
              <span style="display: inline-block; margin-left: 6px; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 4px; background: #FDE8E8; color: #9B1C1C;">Thiếu tồn</span>
            @endif
          </td>
          <td style="padding: 12px 16px; color: #241417; font-weight: 500;">{{ $db->kho?->Ten_kho ?? $db->Ma_kho }}</td>
          <td style="padding: 12px 16px; color: #241417;">{{ $db->khachHang?->Ten_khach_hang ?? '—' }}</td>
          <td style="padding: 12px 16px; color: #241417;">
            {{ $db->Ten_nguoi_nhan }}<br>
            <span style="font-size: 12px; color: #6B5559;">{{ $db->Sdt_nguoi_nhan }}</span>
          </td>
          <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">{{ number_format($db->Tong_tien_hang) }} đ</td>
          <td style="padding: 12px 16px; color: #6B5559; white-space: nowrap;">{{ $db->Thoi_diem_xac_nhan?->format('d/m/Y H:i') ?? '—' }}</td>
          <td style="padding: 12px 16px; text-align: center;">
            <span style="display: inline-block; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; white-space: nowrap; {{ $badgeStyle }}">{{ $db->Trang_thai }}</span>
          </td>
          <td style="padding: 12px 16px; text-align: center;">
            <a href="{{ route('kinh-doanh.don-ban.show', $db->Ma_don_hang_ban) }}" style="display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600; color: #8B1E2D; text-decoration: none;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              Xem
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="padding: 40px; text-align: center; color: #6B5559;">Không có đơn hàng nào.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($donBans->hasPages())
  <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
    {{ $donBans->links() }}
  </div>
  @endif

</div>
@endsection

