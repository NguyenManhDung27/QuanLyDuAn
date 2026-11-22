@extends('layouts.app')

@section('title', 'Đề xuất nhập kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Nút Tạo đề xuất --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Mua hàng</span> &nbsp;/&nbsp; <strong style="color: #241417;">Đề xuất nhập kho</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Đề xuất nhập kho</h1>
    </div>

    <a href="{{ route('kho.de-xuat.create') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Tạo đề xuất</span>
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
  <div role="tablist" aria-label="Trạng thái" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        'Tất cả' => 'Tất cả',
        'Đang cập nhật' => 'Đang cập nhật',
        'Chờ phê duyệt' => 'Chờ phê duyệt',
        'Từ chối' => 'Từ chối',
        'Đề xuất đã hủy' => 'Đề xuất đã hủy'
      ];
    @endphp
    @foreach($tabs as $tabKey => $tabLabel)
      @php $isSel = ($statusTab === $tabKey); @endphp
      <a href="{{ route('kho.de-xuat.index', array_merge(request()->query(), ['tab' => $tabKey])) }}" style="display: inline-flex; align-items: center; height: 38px; padding: 0 14px; border-radius: 999px; font-size: 13px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; {{ $isSel ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        {{ $tabLabel }} <span style="opacity: 0.8; margin-left: 4px;">({{ $counts[$tabKey] ?? 0 }})</span>
      </a>
    @endforeach
  </div>

  {{-- Form tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('kho.de-xuat.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <input type="hidden" name="tab" value="{{ $statusTab }}">

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 2 1 240px; font-size: 13px; font-weight: 500;">
      Tìm kiếm
      <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã đề xuất, nhà cung cấp" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 150px; font-size: 13px; font-weight: 500;">
      Từ ngày lập
      <input type="date" name="tu_ngay" value="{{ $filters['tu_ngay'] }}" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 150px; font-size: 13px; font-weight: 500;">
      Đến ngày lập
      <input type="date" name="den_ngay" value="{{ $filters['den_ngay'] }}" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
      Lọc
    </button>

    @if($filters['search'] || $filters['tu_ngay'] || $filters['den_ngay'] || $statusTab !== 'Tất cả')
      <a href="{{ route('kho.de-xuat.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font-size: 14px; text-decoration: none;">
        Đặt lại
      </a>
    @endif
  </form>

  {{-- Bảng danh sách đề xuất --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <table style="width: 100%; min-width: 960px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Mã đề xuất</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Nhà cung cấp</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 110px;">Ngày lập</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Dự kiến nhận đủ</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right; width: 150px;">Tổng giá trị (VNĐ)</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 130px;">Trạng thái</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Ghi chú</th>
          <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 90px; text-align: center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($proposals as $p)
          @php
            $badges = [
              'Đang cập nhật' => 'background: #EEEAE9; color: #4A3F41;',
              'Chờ phê duyệt' => 'background: #FCEFD9; color: #8A4B08;',
              'Từ chối' => 'background: #F6E1E1; color: #8B1E2D;',
              'Đề xuất đã hủy' => 'background: #EEEAE9; color: #6B5559;',
            ];
            $badgeStyle = $badges[$p->Trang_thai] ?? 'background: #EEEAE9; color: #4A3F41;';

            $note = '';
            if ($p->Trang_thai === 'Từ chối') {
                $note = 'Lý do: ' . $p->Ly_do_tu_choi;
            } elseif ($p->Trang_thai === 'Đề xuất đã hủy') {
                $note = 'Lý do: ' . $p->Ly_do_huy;
            } elseif ($p->Trang_thai === 'Chờ phê duyệt') {
                $note = 'Đang chờ Kế toán';
            } elseif ($p->Trang_thai === 'Đang cập nhật') {
                $note = 'Chưa gửi duyệt';
            }

            $editLink = in_array($p->Trang_thai, ['Đang cập nhật', 'Từ chối'])
              ? route('kho.de-xuat.edit', $p->Ma_don_dat_hang)
              : route('kho.de-xuat.show', $p->Ma_don_dat_hang);

            $actionText = in_array($p->Trang_thai, ['Đang cập nhật', 'Từ chối']) ? 'Sửa' : 'Xem';
          @endphp
          <tr style="border-top: 1px solid #F0E6E3; transition: background 0.15s ease;" onmouseover="this.style.background='#FDFBFB';" onmouseout="this.style.background='transparent';">
            <td style="padding: 12px 16px; font-weight: 600; color: #241417; font-family: monospace;">
              {{ $p->Ma_don_dat_hang }}
            </td>
            <td style="padding: 12px 16px; color: #241417; font-weight: 500;">
              {{ $p->nhaCungCap->Ten_ncc ?? $p->Ma_ncc }}
            </td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $p->Ngay_lap ? \Carbon\Carbon::parse($p->Ngay_lap)->format('d/m/Y') : '—' }}
            </td>
            <td style="padding: 12px 16px; color: #241417;">
              {{ $p->Ngay_du_kien_nhan_du ? \Carbon\Carbon::parse($p->Ngay_du_kien_nhan_du)->format('d/m/Y') : '—' }}
            </td>
            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #6E1423;">
              {{ number_format($p->Tong_gia_tri) }}
            </td>
            <td style="padding: 12px 16px;">
              <span style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $badgeStyle }}">
                {{ $p->Trang_thai }}
              </span>
            </td>
            <td style="padding: 12px 16px; color: #6B5559; font-size: 13px;">
              {{ $note }}
            </td>
            <td style="padding: 12px 16px; text-align: center;">
              <a href="{{ $editLink }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                {{ $actionText }}
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding: 32px 16px; text-align: center; color: #8C7075;">
              Không tìm thấy đề xuất nào phù hợp.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

