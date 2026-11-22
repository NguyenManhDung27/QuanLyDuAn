@extends('layouts.app')

@section('title', 'Chi tiết phiếu nhập kho ' . $slip->Ma_phieu_nhap . ' – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <a href="{{ route('nv-kho.phieu-nhap.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>Phiếu nhập kho</span>
      </a>

      <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Phiếu nhập kho {{ $slip->Ma_phieu_nhap }}</h1>
        <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; {{ $slip->Trang_thai === 'Đã nhập kho' ? 'background: #E5F2EA; color: #1E5B36;' : 'background: #EEEAE9; color: #4A3F41;' }}">
          {{ $slip->Trang_thai }}
        </span>
        @if($blocked)
          <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; background: #FFFFFF; border: 1px solid #B45309; color: #8A4B08;">
            Chờ kiểm kê xong (Lô {{ $blocked['Ma_lo'] }})
          </span>
        @endif
      </div>
    </div>

    {{-- Actions khi Đang cập nhật --}}
    @if($slip->Trang_thai === 'Đang cập nhật')
      <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <form method="POST" action="{{ route('nv-kho.phieu-nhap.destroy', $slip->Ma_phieu_nhap) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu nhập kho này?');">
          @csrf
          @method('DELETE')
          <button type="submit"
                  style="height: 42px; padding: 0 16px; border-radius: 8px; border: 0; background: #F6E1E1; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Xóa phiếu
          </button>
        </form>

        @if($blocked)
          <button type="button" disabled title="Lô {{ $blocked['Ma_lo'] }} đang thuộc phiếu kiểm kê {{ $blocked['Ma_phieu_kiem_ke'] }} chưa hoàn tất."
                  style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #D1D5DB; color: #9CA3AF; font: inherit; font-size: 14px; font-weight: 600; cursor: not-allowed;">
            Xác nhận nhập kho
          </button>
        @else
          <form method="POST" action="{{ route('nv-kho.phieu-nhap.xac-nhan', $slip->Ma_phieu_nhap) }}" onsubmit="return confirm('Xác nhận nhập kho? Sau khi xác nhận hàng sẽ được cộng vào tồn thực và tính lại giá vốn.');">
            @csrf
            <button type="submit"
                    style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
              Xác nhận nhập kho
            </button>
          </form>
        @endif
      </div>
    @endif
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

  {{-- Card Thông tin chung --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; font-size: 14px;">
      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Kho nhận</span>
        <span style="font-weight: 600; color: #241417;">{{ $slip->kho->Ten_kho ?? $slip->Ma_kho }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Loại nhập</span>
        <span style="font-weight: 600; color: #241417;">{{ $slip->Loai_nhap }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Chứng từ gốc</span>
        <span style="font-weight: 600; color: #241417;">
          @if($slip->Loai_nhap === 'Nhập từ NCC')
            Đơn đặt hàng {{ $slip->Ma_don_dat_hang }}
            @if($slip->donDatHang?->nhaCungCap)
              ({{ $slip->donDatHang->nhaCungCap->Ten_ncc }})
            @endif
          @else
            Phiếu xuất {{ $slip->Ma_phieu_xuat }}
          @endif
        </span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Người lập · Ngày lập</span>
        <span style="font-weight: 500; color: #241417;">
          {{ $slip->nguoiLap->Ho_ten ?? $slip->Ma_nguoi_lap }} · {{ $slip->Ngay_lap ? date('d/m/Y', strtotime($slip->Ngay_lap)) : '—' }}
        </span>
      </div>

      @if($slip->Thoi_diem_xac_nhan)
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-size: 12px; color: #6B5559;">Thời điểm xác nhận</span>
          <span style="font-weight: 600; color: #1E5B36;">{{ date('d/m/Y H:i', strtotime($slip->Thoi_diem_xac_nhan)) }}</span>
        </div>
      @endif

      @if($slip->So_phieu_giao_ncc)
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-size: 12px; color: #6B5559;">Số phiếu giao NCC</span>
          <span style="font-weight: 500; color: #241417;">{{ $slip->So_phieu_giao_ncc }}</span>
        </div>
      @endif

      @if($slip->Dot_giao_cuoi)
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-size: 12px; color: #6B5559;">Đợt giao cuối</span>
          <span style="display: inline-flex; width: fit-content; padding: 2px 8px; border-radius: 4px; background: #FEF3C7; color: #92400E; font-weight: 600; font-size: 12px;">Đợt cuối (NCC không giao thêm)</span>
        </div>
      @endif
    </div>

    @if($slip->Ly_do_tra_hang)
      <div style="font-size: 14px; padding-top: 12px; border-top: 1px solid #F0E6E3; color: #241417;">
        <span style="color: #6B5559;">Lý do hàng hoàn / trả:</span> {{ $slip->Ly_do_tra_hang }}
      </div>
    @endif
  </section>

  {{-- Card Danh sách sản phẩm & Lô --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Chi tiết lô hàng nhập</h2>

    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã lô nội bộ</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã lô NCC</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Hạn sử dụng</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">SL nhận đạt</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Hao hụt</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Lý do hao hụt</th>
          </tr>
        </thead>
        <tbody>
          @foreach($slip->chiTiets as $ct)
            @php
              $lo = $ct->lo;
              $sp = $lo?->sanPham;
            @endphp
            <tr style="border-top: 1px solid #F0E6E3;">
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                <div>{{ $sp->Ten_san_pham ?? '—' }}</div>
                <div style="font-size: 12px; color: #6B5559;">
                  {{ $sp->Ma_san_pham ?? '' }} · {{ $sp->Nien_vu ?? '' }} · {{ $sp->Dung_tich_ml ?? '' }}ml
                </div>
              </td>
              <td style="padding: 12px 16px; font-weight: 600; color: #241417;">{{ $ct->Ma_lo }}</td>
              <td style="padding: 12px 16px; color: #4A3A3D;">{{ $lo->Ma_lo_ncc ?: '—' }}</td>
              <td style="padding: 12px 16px; color: #4A3A3D;">{{ $lo->Han_su_dung ? date('d/m/Y', strtotime($lo->Han_su_dung)) : '—' }}</td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #1E5B36;">{{ number_format($ct->So_luong_nhap) }}</td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $ct->So_luong_hao_hut > 0 ? '#B45309;' : '#6B5559;' }}">
                {{ number_format($ct->So_luong_hao_hut) }}
              </td>
              <td style="padding: 12px 16px; color: #4A3A3D;">{{ $ct->Ly_do_hao_hut ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="display: flex; justify-content: flex-end; align-items: baseline; gap: 16px; padding-top: 12px; font-size: 15px; border-top: 1px solid #F0E6E3;">
      <span style="color: #6B5559;">Tổng nhận đạt: <strong style="color: #1E5B36;">{{ number_format($slip->chiTiets->sum('So_luong_nhap')) }} chai</strong></span>
      @if($slip->chiTiets->sum('So_luong_hao_hut') > 0)
        <span style="color: #6B5559;">Tổng hao hụt: <strong style="color: #B45309;">{{ number_format($slip->chiTiets->sum('So_luong_hao_hut')) }} chai</strong></span>
      @endif
    </div>
  </section>
</div>
@endsection

