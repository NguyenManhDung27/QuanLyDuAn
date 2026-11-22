@extends('layouts.app')

@section('title', 'Đề xuất nhập kho – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <div style="font-size: 13px; color: #6B5559;">
        <span>Phê duyệt</span> &nbsp;/&nbsp; <strong style="color: #241417;">Đề xuất nhập kho</strong>
      </div>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Đề xuất nhập kho</h1>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Đề xuất do Quản lý kho 2 kho gửi lên.</p>
    </div>
  </div>

  {{-- Thông báo Alerts --}}
  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px;">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      {{ session('error') }}
    </div>
  @endif

  {{-- Tabs trạng thái --}}
  <div role="tablist" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabList = [
        'Chờ phê duyệt' => 'Chờ phê duyệt',
        'Đã duyệt' => 'Đã duyệt',
        'Từ chối' => 'Từ chối',
        'Tất cả' => 'Tất cả'
      ];
    @endphp

    @foreach($tabList as $key => $label)
      @php
        $isActive = ($statusTab === $key);
        $count = $counts[$key] ?? 0;
      @endphp
      <a href="{{ route('ke-toan.de-xuat.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
         role="tab"
         style="display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; {{ $isActive ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        <span>{{ $label }}</span>
        <span style="opacity: 0.8; font-size: 13px;">({{ $count }})</span>
      </a>
    @endforeach
  </div>

  {{-- Form tìm kiếm và lọc --}}
  <form method="GET" action="{{ route('ke-toan.de-xuat.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; background: #FFFFFF; padding: 16px; border-radius: 10px; border: 1px solid #EADFDC;">
    <input type="hidden" name="tab" value="{{ $statusTab }}">

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 220px;">
      <span>Tìm kiếm</span>
      <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã đề xuất, nhà cung cấp..."
             style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 160px;">
      <span>Kho</span>
      <select name="kho" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả" {{ $filters['kho'] === 'Tất cả' ? 'selected' : '' }}>Tất cả kho</option>
        @foreach($khos as $k)
          <option value="{{ $k->Ma_kho }}" {{ $filters['kho'] === $k->Ma_kho ? 'selected' : '' }}>{{ $k->Ten_kho }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
      <span>Từ ngày</span>
      <input type="date" name="tu_ngay" value="{{ $filters['tu_ngay'] }}"
             style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
      <span>Đến ngày</span>
      <input type="date" name="den_ngay" value="{{ $filters['den_ngay'] }}"
             style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <div style="display: flex; gap: 8px;">
      <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
        Lọc
      </button>
      <a href="{{ route('ke-toan.de-xuat.index', ['tab' => $statusTab]) }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">
        Đặt lại
      </a>
    </div>
  </form>

  {{-- Bảng danh sách đề xuất --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <table style="width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 14px; text-align: left;">
      <thead>
        <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã đề xuất</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Kho</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Nhà cung cấp</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Người lập</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Ngày gửi duyệt</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Dự kiến nhận đủ</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Tổng giá trị (VNĐ)</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($proposals as $p)
          @php
            $badgeStyle = match($p->Trang_thai) {
              'Chờ phê duyệt' => 'background: #FCEFD9; color: #8A4B08;',
              'Từ chối' => 'background: #F6E1E1; color: #8B1E2D;',
              'Đóng thiếu' => 'background: #E0F2FE; color: #0369A1;',
              'Đơn đã hủy' => 'background: #F3F4F6; color: #6B7280;',
              default => 'background: #E5F2EA; color: #1E5B36;', // Chưa gửi NCC, Đã gửi NCC, Nhận một phần, Đã nhận đủ
            };
          @endphp
          <tr style="border-top: 1px solid #F0E6E3; transition: background 0.15s ease;" onmouseover="this.style.background='#FDFBFB';" onmouseout="this.style.background='transparent';">
            <td style="padding: 12px 16px; font-weight: 600; color: #241417;">{{ $p->Ma_don_dat_hang }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $p->kho->Ten_kho ?? $p->Ma_kho }}</td>
            <td style="padding: 12px 16px; color: #241417;">{{ $p->nhaCungCap->Ten_ncc ?? $p->Ma_ncc }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $p->nguoiLap->Ho_ten ?? $p->Ma_nguoi_lap }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $p->Ngay_lap ? date('d/m/Y', strtotime($p->Ngay_lap)) : '—' }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $p->Ngay_du_kien_nhan ? date('d/m/Y', strtotime($p->Ngay_du_kien_nhan)) : '—' }}</td>
            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">{{ number_format($p->Tong_tien, 0, ',', '.') }}</td>
            <td style="padding: 12px 16px;">
              <span style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $badgeStyle }}">
                {{ $p->Trang_thai }}
              </span>
            </td>
            <td style="padding: 12px 16px; text-align: center;">
              <a href="{{ route('ke-toan.de-xuat.show', $p->Ma_don_dat_hang) }}"
                 style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; {{ $p->Trang_thai === 'Chờ phê duyệt' ? 'background: #8B1E2D; color: #FFFFFF;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #8B1E2D;' }}">
                {{ $p->Trang_thai === 'Chờ phê duyệt' ? 'Duyệt' : 'Xem' }}
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" style="padding: 40px 16px; text-align: center; color: #6B5559;">
              <div style="font-size: 15px; font-weight: 500; margin-bottom: 4px;">Không tìm thấy đề xuất nhập kho nào</div>
              <div style="font-size: 13px;">Hãy thử thay đổi điều kiện lọc hoặc từ khóa tìm kiếm.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

