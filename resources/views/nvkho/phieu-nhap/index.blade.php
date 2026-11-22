@extends('layouts.app')

@section('title', 'Phiếu nhập kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <div style="font-size: 13px; color: #6B5559;">
        <span>Nhập kho</span> &nbsp;/&nbsp; <strong style="color: #241417;">Phiếu nhập kho</strong>
      </div>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Phiếu nhập kho</h1>
    </div>

    <a href="{{ route('nv-kho.nhap-kho.index') }}"
       style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;"
       onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Nhập kho</span>
    </a>
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
        'Tất cả' => 'Tất cả',
        'Đang cập nhật' => 'Đang cập nhật',
        'Đã nhập kho' => 'Đã nhập kho'
      ];
    @endphp

    @foreach($tabList as $key => $label)
      @php
        $isActive = ($statusTab === $key);
        $count = $counts[$key] ?? 0;
      @endphp
      <a href="{{ route('nv-kho.phieu-nhap.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}"
         role="tab"
         style="display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; {{ $isActive ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        <span>{{ $label }}</span>
        <span style="opacity: 0.8; font-size: 13px;">({{ $count }})</span>
      </a>
    @endforeach
  </div>

  {{-- Form tìm kiếm và bộ lọc --}}
  <form method="GET" action="{{ route('nv-kho.phieu-nhap.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; background: #FFFFFF; padding: 16px; border-radius: 10px; border: 1px solid #EADFDC;">
    <input type="hidden" name="tab" value="{{ $statusTab }}">

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 220px;">
      <span>Tìm kiếm</span>
      <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã phiếu, mã đơn đặt hàng, mã phiếu xuất..."
             style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 180px;">
      <span>Loại nhập</span>
      <select name="loai_nhap" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả" {{ $filters['loai_nhap'] === 'Tất cả' ? 'selected' : '' }}>Tất cả</option>
        <option value="Nhập từ NCC" {{ $filters['loai_nhap'] === 'Nhập từ NCC' ? 'selected' : '' }}>Nhập từ NCC</option>
        <option value="Khách trả hàng" {{ $filters['loai_nhap'] === 'Khách trả hàng' ? 'selected' : '' }}>Khách trả hàng</option>
        <option value="Hoàn giao thất bại" {{ $filters['loai_nhap'] === 'Hoàn giao thất bại' ? 'selected' : '' }}>Hoàn giao thất bại</option>
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
      <a href="{{ route('nv-kho.phieu-nhap.index', ['tab' => $statusTab]) }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">
        Đặt lại
      </a>
    </div>
  </form>

  {{-- Bảng phiếu nhập kho --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <table style="width: 100%; min-width: 980px; border-collapse: collapse; font-size: 14px; text-align: left;">
      <thead>
        <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã phiếu</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Loại nhập</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Chứng từ gốc</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Ngày lập</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Người lập</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">SL nhập</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($slips as $s)
          @php
            $isBlocked = $blockedMap[$s->Ma_phieu_nhap] ?? false;
            $slNhap = $s->chiTiets->sum('So_luong_nhap');
            $chungTuGoc = match($s->Loai_nhap) {
              'Nhập từ NCC' => 'Đơn đặt hàng ' . $s->Ma_don_dat_hang,
              default => 'Phiếu xuất ' . $s->Ma_phieu_xuat,
            };
          @endphp
          <tr style="border-top: 1px solid #F0E6E3; transition: background 0.15s ease;" onmouseover="this.style.background='#FDFBFB';" onmouseout="this.style.background='transparent';">
            <td style="padding: 12px 16px; font-weight: 600; color: #241417;">{{ $s->Ma_phieu_nhap }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $s->Loai_nhap }}</td>
            <td style="padding: 12px 16px; color: #241417; font-weight: 500;">{{ $chungTuGoc }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $s->Ngay_lap ? date('d/m/Y', strtotime($s->Ngay_lap)) : '—' }}</td>
            <td style="padding: 12px 16px; color: #4A3A3D;">{{ $s->nguoiLap->Ho_ten ?? $s->Ma_nguoi_lap }}</td>
            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">{{ number_format($slNhap) }}</td>
            <td style="padding: 12px 16px;">
              <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                <span style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $s->Trang_thai === 'Đã nhập kho' ? 'background: #E5F2EA; color: #1E5B36;' : 'background: #EEEAE9; color: #4A3F41;' }}">
                  {{ $s->Trang_thai }}
                </span>
                @if($isBlocked)
                  <span title="Có lô đang thuộc phiếu kiểm kê chưa hoàn tất" style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #FFFFFF; border: 1px solid #B45309; color: #8A4B08; white-space: nowrap;">
                    Chờ kiểm kê xong
                  </span>
                @endif
              </div>
            </td>
            <td style="padding: 12px 16px; text-align: center;">
              <a href="{{ route('nv-kho.phieu-nhap.show', $s->Ma_phieu_nhap) }}"
                 style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; {{ $s->Trang_thai === 'Đang cập nhật' ? 'background: #8B1E2D; color: #FFFFFF;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #8B1E2D;' }}">
                {{ $s->Trang_thai === 'Đang cập nhật' ? 'Tiếp tục' : 'Xem' }}
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding: 40px 16px; text-align: center; color: #6B5559;">
              <div style="font-size: 15px; font-weight: 500; margin-bottom: 4px;">Không tìm thấy phiếu nhập kho nào</div>
              <div style="font-size: 13px;">Hãy thử thay đổi điều kiện lọc hoặc tạo phiếu nhập mới.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <span style="font-size: 13px; color: #6B5559;">
    * "Chờ kiểm kê xong": phiếu có lô đang nằm trong phiếu kiểm kê chưa hoàn tất nên chưa xác nhận nhập được; mở lại để xác nhận khi kiểm kê xong.
  </span>
</div>
@endsection

