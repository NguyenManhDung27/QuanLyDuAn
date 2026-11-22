@extends('layouts.app')

@section('title', 'Đơn đặt hàng – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb, Title & Subtitle --}}
  <div style="display: flex; flex-direction: column; gap: 6px;">
    <div style="font-size: 13px; color: #6B5559;">
      <span>Mua hàng</span> &nbsp;/&nbsp; <strong style="color: #241417;">Đơn đặt hàng</strong>
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Đơn đặt hàng</h1>
    <p style="margin: 0; font-size: 14px; color: #6B5559;">Đề xuất đã được Kế toán duyệt sẽ chuyển sang danh sách này, giữ nguyên mã.</p>
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
  <div role="tablist" aria-label="Trạng thái" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        'Tất cả' => 'Tất cả',
        'Chưa gửi NCC' => 'Chưa gửi NCC',
        'Đã gửi NCC' => 'Đã gửi NCC',
        'Nhận một phần' => 'Nhận một phần',
        'Đã nhận đủ' => 'Đã nhận đủ',
        'Đóng thiếu' => 'Đóng thiếu',
        'Đơn đã hủy' => 'Đơn đã hủy'
      ];
    @endphp
    @foreach($tabs as $tabKey => $tabLabel)
      @php $isSel = ($statusTab === $tabKey); @endphp
      <a href="{{ route('kho.don-dat-hang.index', array_merge(request()->query(), ['tab' => $tabKey])) }}" style="display: inline-flex; align-items: center; height: 38px; padding: 0 14px; border-radius: 999px; font-size: 13px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; {{ $isSel ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        {{ $tabLabel }} <span style="opacity: 0.8; margin-left: 4px;">({{ $counts[$tabKey] ?? 0 }})</span>
      </a>
    @endforeach
  </div>

  {{-- Form tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('kho.don-dat-hang.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <input type="hidden" name="tab" value="{{ $statusTab }}">

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 2 1 240px; font-size: 13px; font-weight: 500;">
      Tìm kiếm
      <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã đơn, nhà cung cấp" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; align-items: center; gap: 10px; height: 42px; font-size: 14px; font-weight: 500; cursor: pointer;">
      <input type="checkbox" name="can_xem_xet" value="1" {{ $filters['can_xem_xet'] ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #8B1E2D; cursor: pointer;">
      <span>Chỉ hiện đơn cần xem xét</span>
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
      Lọc
    </button>

    @if($filters['search'] || $filters['can_xem_xet'] || $statusTab !== 'Tất cả')
      <a href="{{ route('kho.don-dat-hang.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font-size: 14px; text-decoration: none;">
        Đặt lại
      </a>
    @endif
  </form>

  {{-- Bảng danh sách đơn đặt hàng --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <table style="width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Mã đơn</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Nhà cung cấp</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 120px;">Ngày gửi NCC</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Dự kiến nhận đủ</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 220px;">Tiến độ nhận</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 180px;">Trạng thái</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 90px; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
          @php
            $badges = [
              'Chưa gửi NCC' => 'background: #E3EAFB; color: #1D3F9E;',
              'Đã gửi NCC' => 'background: #E3EAFB; color: #1D3F9E;',
              'Nhận một phần' => 'background: #FCEFD9; color: #8A4B08;',
              'Đã nhận đủ' => 'background: #E5F2EA; color: #1E5B36;',
              'Đóng thiếu' => 'background: #EEEAE9; color: #4A3F41;',
              'Đơn đã hủy' => 'background: #EEEAE9; color: #6B5559;',
            ];
            $badgeStyle = $badges[$o->Trang_thai] ?? 'background: #EEEAE9; color: #4A3F41;';

            $tongDat = $o->tong_so_luong_dat;
            $tongNhan = $o->tong_da_nhan;
            $pct = $tongDat > 0 ? min(100, round(($tongNhan / $tongDat) * 100)) : 0;
            $barColor = ($pct >= 100) ? '#1E5B36' : '#8B1E2D';
          @endphp
          <tr style="border-top: 1px solid #F0E6E3; transition: background 0.15s ease;" onmouseover="this.style.background='#FDFBFB';" onmouseout="this.style.background='transparent';">
            <td style="padding: 12px 16px; font-weight: 600; color: #241417; font-family: monospace;">
              {{ $o->Ma_don_dat_hang }}
            </td>
            <td style="padding: 12px 16px; color: #241417; font-weight: 500;">
              {{ $o->nhaCungCap->Ten_ncc ?? $o->Ma_ncc }}
            </td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $o->Ngay_gui_ncc ? \Carbon\Carbon::parse($o->Ngay_gui_ncc)->format('d/m/Y') : '—' }}
            </td>
            <td style="padding: 12px 16px; color: #241417;">
              {{ $o->Ngay_du_kien_nhan_du ? \Carbon\Carbon::parse($o->Ngay_du_kien_nhan_du)->format('d/m/Y') : '—' }}
            </td>
            <td style="padding: 12px 16px;">
              <div style="display: flex; flex-direction: column; gap: 6px; min-width: 150px;">
                <div style="height: 7px; border-radius: 999px; background: #F0E6E3; overflow: hidden;">
                  <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 999px;"></div>
                </div>
                <span style="font-size: 12px; color: #6B5559;">
                  Đã nhận {{ number_format($tongNhan) }} / {{ number_format($tongDat) }} chai ({{ $pct }}%)
                </span>
              </div>
            </td>
            <td style="padding: 12px 16px;">
              <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                <span style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $badgeStyle }}">
                  {{ $o->Trang_thai }}
                </span>
                @if($o->can_xem_xet)
                  <span style="display: inline-flex; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #FFFFFF; border: 1px solid #B45309; color: #8A4B08; white-space: nowrap;">
                    Cần xem xét
                  </span>
                @endif
              </div>
            </td>
            <td style="padding: 12px 16px; text-align: center;">
              <a href="{{ route('kho.de-xuat.show', $o->Ma_don_dat_hang) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                Xem
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="padding: 32px 16px; text-align: center; color: #8C7075;">
              Không tìm thấy đơn đặt hàng nào phù hợp.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

