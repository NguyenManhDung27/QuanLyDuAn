@extends('layouts.app')

@php
  $isOrder = in_array($don->Trang_thai, ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần', 'Đã nhận đủ', 'Đóng thiếu', 'Đơn đã hủy']);
  $pageTitle = ($isOrder ? 'Đơn đặt hàng ' : 'Đề xuất ') . $don->Ma_don_dat_hang;
  $backUrl = $isOrder ? route('kho.don-dat-hang.index') : route('kho.de-xuat.index');
  $backLabel = $isOrder ? 'Đơn đặt hàng' : 'Đề xuất nhập kho';

  $badges = [
    'Đang cập nhật' => 'background: #EEEAE9; color: #4A3F41;',
    'Chờ phê duyệt' => 'background: #FCEFD9; color: #8A4B08;',
    'Từ chối' => 'background: #F6E1E1; color: #8B1E2D;',
    'Đề xuất đã hủy' => 'background: #EEEAE9; color: #6B5559;',
    'Chưa gửi NCC' => 'background: #E3EAFB; color: #1D3F9E;',
    'Đã gửi NCC' => 'background: #E3EAFB; color: #1D3F9E;',
    'Nhận một phần' => 'background: #FCEFD9; color: #8A4B08;',
    'Đã nhận đủ' => 'background: #E5F2EA; color: #1E5B36;',
    'Đóng thiếu' => 'background: #EEEAE9; color: #4A3F41;',
    'Đơn đã hủy' => 'background: #EEEAE9; color: #6B5559;',
  ];
  $badgeStyle = $badges[$don->Trang_thai] ?? 'background: #EEEAE9; color: #4A3F41;';
@endphp

@section('title', $pageTitle . ' – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Back link, Title, Badges & Nút Hành động --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <a href="{{ $backUrl }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500;">
        ← {{ $backLabel }}
      </a>
      <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">{{ $pageTitle }}</h1>
        <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; {{ $badgeStyle }}">
          {{ $don->Trang_thai }}
        </span>
        @if($don->can_xem_xet)
          <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; background: #FFFFFF; border: 1px solid #B45309; color: #8A4B08;">
            Cần xem xét
          </span>
        @endif
      </div>
    </div>

    {{-- Nút hành động theo trạng thái --}}
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
      @if($don->Trang_thai === 'Chờ phê duyệt')
        <form method="POST" action="{{ route('kho.de-xuat.thu-hoi', $don->Ma_don_dat_hang) }}" onsubmit="return confirm('Thu hồi đề xuất về trạng thái Đang cập nhật để sửa?');">
          @csrf
          <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Thu hồi
          </button>
        </form>
      @elseif($don->Trang_thai === 'Chưa gửi NCC')
        <button type="button" onclick="document.getElementById('cancelOrderModal').style.display='flex';" style="height: 42px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Hủy đơn
        </button>

        <form method="POST" action="{{ route('kho.don-dat-hang.gui-ncc', $don->Ma_don_dat_hang) }}" onsubmit="return confirm('Xác nhận đã gửi đơn cho nhà cung cấp? Từ lúc này đơn không sửa hay hủy được nữa.');">
          @csrf
          <button type="submit" style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08);">
            Xác nhận đã gửi NCC
          </button>
        </form>
      @elseif(in_array($don->Trang_thai, ['Đã gửi NCC', 'Nhận một phần']))
        <button type="button" onclick="document.getElementById('updateDateModal').style.display='flex';" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Cập nhật ngày dự kiến nhận đủ
        </button>

        <button type="button" onclick="document.getElementById('closeShortModal').style.display='flex';" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Đóng thiếu
        </button>
      @endif
    </div>
  </div>

  {{-- Banner thông báo theo trạng thái --}}
  @if($don->Trang_thai === 'Chờ phê duyệt')
    <div style="padding: 14px 18px; border-radius: 10px; background: #FDF6EA; border: 1px solid #F0D9B0; font-size: 14px; color: #3B2A2D;">
      Đề xuất đang chờ Kế toán duyệt nên không sửa được. Muốn sửa, bấm <strong>Thu hồi</strong> để đưa về Đang cập nhật.
    </div>
  @elseif($don->Trang_thai === 'Chưa gửi NCC')
    <div style="padding: 14px 18px; border-radius: 10px; background: #EEF3FD; border: 1px solid #C9D6F5; font-size: 14px; color: #3B2A2D;">
      Kế toán đã duyệt. Gửi đơn cho nhà cung cấp ngoài hệ thống (email, điện thoại), sau đó bấm <strong>Xác nhận đã gửi NCC</strong>.
    </div>
  @elseif($don->Trang_thai === 'Đóng thiếu')
    <div style="padding: 14px 18px; border-radius: 10px; background: #F6F1EF; border: 1px solid #EADFDC; font-size: 14px; color: #3B2A2D;">
      Đơn đã đóng thiếu vào {{ $don->Thoi_diem_dong_thieu ? $don->Thoi_diem_dong_thieu->format('d/m/Y H:i') : '' }}.
      Lý do: <strong>{{ $don->Ly_do_dong_thieu }}</strong>. Phần còn lại sẽ không nhận nữa.
    </div>
  @elseif(in_array($don->Trang_thai, ['Đề xuất đã hủy', 'Đơn đã hủy']))
    <div style="padding: 14px 18px; border-radius: 10px; background: #F6F1EF; border: 1px solid #EADFDC; font-size: 14px; color: #3B2A2D;">
      {{ $don->Trang_thai }} vào {{ $don->Thoi_diem_huy ? $don->Thoi_diem_huy->format('d/m/Y H:i') : '' }}.
      Lý do: <strong>{{ $don->Ly_do_huy }}</strong>.
    </div>
  @endif

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

  {{-- Section 1: Thông tin chung --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Thông tin chung</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; font-size: 14px;">
      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Mã</span>
        <span style="font-weight: 600; font-family: monospace; color: #241417;">{{ $don->Ma_don_dat_hang }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Nhà cung cấp</span>
        <span style="font-weight: 500; color: #241417;">{{ $don->nhaCungCap->Ten_ncc ?? $don->Ma_ncc }} ({{ $don->nhaCungCap->Quoc_gia ?? '' }})</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Kho nhận</span>
        <span style="font-weight: 500; color: #241417;">{{ $don->kho->Ten_kho ?? $don->Ma_kho }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Ngày dự kiến nhận đủ</span>
        <span style="font-weight: 500; color: #241417;">{{ $don->Ngay_du_kien_nhan_du ? $don->Ngay_du_kien_nhan_du->format('d/m/Y') : '—' }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Người lập · Ngày lập</span>
        <span style="font-weight: 500; color: #241417;">{{ $don->nguoiLap->Ho_ten ?? '—' }} · {{ $don->Ngay_lap ? $don->Ngay_lap->format('d/m/Y') : '—' }}</span>
      </div>

      @if($isOrder)
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-size: 12px; color: #6B5559;">Người duyệt · Ngày gửi NCC</span>
          <span style="font-weight: 500; color: #241417;">
            {{ $don->nguoiDuyet->Ho_ten ?? '—' }} · {{ $don->Ngay_gui_ncc ? $don->Ngay_gui_ncc->format('d/m/Y') : 'Chưa gửi' }}
          </span>
        </div>
      @endif
    </div>

    @if($don->Ghi_chu_phe_duyet)
      <div style="font-size: 14px; padding-top: 10px; border-top: 1px solid #F0E6E3;">
        <span style="color: #6B5559;">Ghi chú:</span> {{ $don->Ghi_chu_phe_duyet }}
      </div>
    @endif
  </section>

  {{-- Section 2: Sản phẩm đặt mua --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Sản phẩm đặt mua</h2>

    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="background: #F6ECEA; text-align: left;">
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 110px;">SL đặt</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 150px;">Đơn giá mua</th>
            <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 160px;">Thành tiền</th>
            @if($isOrder)
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 100px;">Đã nhận</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 100px;">Hao hụt</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 100px;">Còn lại</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($don->chiTiets as $ct)
            @php
              $sp = $ct->sanPham;
              $tienDo = $tienDoMap[$ct->Ma_san_pham] ?? ['da_nhan' => 0, 'hao_hut' => 0, 'con_lai' => (int)$ct->So_luong_dat];
            @endphp
            <tr style="border-top: 1px solid #F0E6E3;">
              <td style="padding: 10px 12px; font-weight: 500; color: #241417;">
                {{ $sp->Ten_san_pham ?? $ct->Ma_san_pham }}
                {{ $sp && $sp->Nien_vu ? '– ' . $sp->Nien_vu : '' }}
                {{ $sp && $sp->Dung_tich ? '(' . $sp->Dung_tich . 'ml)' : '' }}
              </td>
              <td style="padding: 10px 12px; text-align: right; color: #241417;">
                {{ number_format($ct->So_luong_dat) }}
              </td>
              <td style="padding: 10px 12px; text-align: right; color: #241417;">
                {{ number_format($ct->Don_gia_mua) }}
              </td>
              <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: #241417;">
                {{ number_format($ct->So_luong_dat * $ct->Don_gia_mua) }}
              </td>
              @if($isOrder)
                <td style="padding: 10px 12px; text-align: right; color: #1E6E38; font-weight: 600;">
                  {{ number_format($tienDo['da_nhan']) }}
                </td>
                <td style="padding: 10px 12px; text-align: right; color: #9B1C1C;">
                  {{ number_format($tienDo['hao_hut']) }}
                </td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: {{ $tienDo['con_lai'] > 0 ? '#8B1E2D' : '#6B5559' }};">
                  {{ number_format($tienDo['con_lai']) }}
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; font-size: 15px; padding-top: 8px; border-top: 1px solid #F0E6E3;">
      <span style="color: #6B5559;">Tổng giá trị (chưa thuế):</span>
      <span style="font-size: 18px; font-weight: 700; color: #6E1423;">
        {{ number_format($don->Tong_gia_tri) }} VNĐ
      </span>
    </div>
  </section>

  {{-- Section 3: Các lần nhận hàng (chỉ khi là đơn đặt hàng) --}}
  @if($isOrder)
    <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
      <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Các lần nhận hàng</h2>

      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 640px; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #F6ECEA; text-align: left;">
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320;">Phiếu nhập</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; width: 140px;">Ngày nhập</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320;">Người nhập</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 120px;">SL đạt</th>
              <th scope="col" style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 120px;">Hao hụt</th>
            </tr>
          </thead>
          <tbody>
            @forelse($receipts as $rc)
              @php
                $slDat = (int) $rc->chiTiets()->sum('So_luong_nhap');
                $slHaoHut = (int) $rc->chiTiets()->sum('So_luong_hao_hut');
              @endphp
              <tr style="border-top: 1px solid #F0E6E3;">
                <td style="padding: 10px 12px; font-weight: 600; font-family: monospace; color: #241417;">
                  {{ $rc->Ma_phieu_nhap }}
                </td>
                <td style="padding: 10px 12px; color: #241417;">
                  {{ $rc->Ngay_lap ? \Carbon\Carbon::parse($rc->Ngay_lap)->format('d/m/Y') : '—' }}
                </td>
                <td style="padding: 10px 12px; color: #241417;">
                  {{ $rc->nguoiLap->Ho_ten ?? '—' }}
                </td>
                <td style="padding: 10px 12px; text-align: right; color: #1E6E38; font-weight: 600;">
                  {{ number_format($slDat) }}
                </td>
                <td style="padding: 10px 12px; text-align: right; color: #9B1C1C;">
                  {{ number_format($slHaoHut) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="padding: 20px 12px; text-align: center; color: #8C7075; font-style: italic;">
                  Chưa có đợt nhận hàng nào được xác nhận cho đơn này.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endif
</div>

{{-- Modal Hủy đơn (khi Chưa gửi NCC) --}}
@if($don->Trang_thai === 'Chưa gửi NCC')
  <div id="cancelOrderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; width: 480px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 16px;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #8B1E2D;">Hủy đơn đặt hàng {{ $don->Ma_don_dat_hang }}</h3>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Đơn đặt hàng sẽ chuyển sang trạng thái <strong>Đơn đã hủy</strong>.</p>

      <form method="POST" action="{{ route('kho.don-dat-hang.huy', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
          Lý do hủy <span style="color: #9B1C1C;">*</span>
          <textarea name="ly_do_huy" required rows="3" placeholder="Nhập lý do hủy đơn..." style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
        </label>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
          <button type="button" onclick="document.getElementById('cancelOrderModal').style.display='none';" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; font: inherit; font-size: 14px; cursor: pointer;">
            Quay lại
          </button>
          <button type="submit" style="height: 40px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Xác nhận hủy
          </button>
        </div>
      </form>
    </div>
  </div>
@endif

{{-- Modal Cập nhật ngày dự kiến nhận đủ --}}
@if(in_array($don->Trang_thai, ['Đã gửi NCC', 'Nhận một phần']))
  <div id="updateDateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; width: 440px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 16px;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #8B1E2D;">Cập nhật ngày dự kiến nhận đủ</h3>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Nhập ngày dự kiến nhận đủ mới theo thông báo của nhà cung cấp.</p>

      <form method="POST" action="{{ route('kho.don-dat-hang.ngay-du-kien', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
          Ngày dự kiến mới <span style="color: #9B1C1C;">*</span>
          <input type="date" name="ngay_du_kien_nhan_du" value="{{ $don->Ngay_du_kien_nhan_du ? $don->Ngay_du_kien_nhan_du->format('Y-m-d') : '' }}" required style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px;">
        </label>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
          <button type="button" onclick="document.getElementById('updateDateModal').style.display='none';" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; font: inherit; font-size: 14px; cursor: pointer;">
            Quay lại
          </button>
          <button type="submit" style="height: 40px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Cập nhật
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Đóng thiếu --}}
  <div id="closeShortModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; width: 480px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 16px;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #8B1E2D;">Đóng thiếu đơn đặt hàng {{ $don->Ma_don_dat_hang }}</h3>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Phần còn lại sẽ không nhận nữa. Chỉ đóng được khi không có phiếu nhập nào đang ở trạng thái Đang cập nhật của đơn.</p>

      <form method="POST" action="{{ route('kho.don-dat-hang.dong-thieu', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500;">
          Lý do đóng thiếu <span style="color: #9B1C1C;">*</span>
          <textarea name="ly_do_dong_thieu" required rows="3" placeholder="Nhập lý do đóng thiếu (VD: Nhà cung cấp hết hàng, báo không giao tiếp)..." style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
        </label>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
          <button type="button" onclick="document.getElementById('closeShortModal').style.display='none';" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; font: inherit; font-size: 14px; cursor: pointer;">
            Quay lại
          </button>
          <button type="submit" style="height: 40px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Xác nhận đóng thiếu
          </button>
        </div>
      </form>
    </div>
  </div>
@endif
@endsection

