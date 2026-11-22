@extends('layouts.app')

@section('title', 'Trang chủ Nhân viên kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  {{-- Tiêu đề trang (theo mẫu Designcompass NVK_0_screen_0) --}}
  <div style="display: flex; flex-direction: column; gap: 6px;">
    <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Việc cần xử lý</h1>
    <p style="margin: 0; font-size: 14px; color: #6B5559;">
      Các việc đang chờ tại <strong style="color: #241417;">{{ $kho->Ten_kho ?? 'Kho' }}</strong>. Bấm vào thẻ để mở danh sách.
    </p>
  </div>

  {{-- Lưới 5 Action Cards --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
    @foreach($cards as $c)
      <a href="{{ $c['href'] }}"
         style="display: flex; flex-direction: column; gap: 10px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; color: #241417; text-decoration: none; min-height: 128px; transition: transform 0.15s ease, box-shadow 0.15s ease;"
         onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';"
         onmouseout="this.style.transform='none';this.style.boxShadow='none';">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
          <span style="font-size: 14px; font-weight: 600; color: #241417;">{{ $c['label'] }}</span>
          <span style="width: 10px; height: 10px; border-radius: 999px; background: {{ $c['dot'] }};"></span>
        </div>
        <span style="font-size: 32px; font-weight: 700; color: {{ $c['count'] > 0 ? '#6E1423' : '#6B5559' }};">
          {{ $c['count'] }}
        </span>
        <span style="font-size: 13px; color: #6B5559;">{{ $c['sub'] }}</span>
      </a>
    @endforeach
  </div>

  {{-- Hai cột: Phiếu xuất cần xử lý & Phiếu nhập đang cập nhật --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
    
    {{-- Phiếu xuất đang chuẩn bị / đang giao --}}
    <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Phiếu xuất cần xử lý</h3>
        <a href="{{ route('nv-kho.phieu-xuat.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
      </div>
      @if($phieuXuatGanDay->isEmpty())
        <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Không có phiếu xuất nào cần xử lý.</div>
      @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
          @foreach($phieuXuatGanDay as $px)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #FAF5F3; border-radius: 8px; font-size: 13px;">
              <div>
                <a href="{{ route('nv-kho.phieu-xuat.show', $px->Ma_phieu_xuat) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                  {{ $px->Ma_phieu_xuat }}
                </a>
                <div style="color: #6B5559; font-size: 12px; margin-top: 2px;">
                  {{ $px->Ten_nguoi_nhan ?? 'Khách lẻ' }} · Đơn {{ $px->Ma_don_hang_ban }}
                </div>
              </div>
              <div style="text-align: right;">
                <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; 
                             {{ $px->Trang_thai_giao_hang === 'Đang chuẩn bị hàng' ? 'background: #FCEFD9; color: #8A4B08;' : 'background: #E3EAFB; color: #1D3F9E;' }}">
                  {{ $px->Trang_thai_giao_hang }}
                </span>
                <div style="color: #6B5559; font-size: 11px; margin-top: 2px;">{{ $px->Ngay_tao ? \Carbon\Carbon::parse($px->Ngay_tao)->format('d/m/Y') : '' }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Phiếu nhập đang cập nhật --}}
    <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Phiếu nhập đang cập nhật</h3>
        <a href="{{ route('nv-kho.phieu-nhap.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
      </div>
      @if($phieuNhapGanDay->isEmpty())
        <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Không có phiếu nhập nháp nào.</div>
      @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
          @foreach($phieuNhapGanDay as $pn)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #FAF5F3; border-radius: 8px; font-size: 13px;">
              <div>
                <a href="{{ route('nv-kho.phieu-nhap.show', $pn->Ma_phieu_nhap) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                  {{ $pn->Ma_phieu_nhap }}
                </a>
                <div style="color: #6B5559; font-size: 12px; margin-top: 2px;">{{ $pn->Loai_nhap }}</div>
              </div>
              <div style="text-align: right;">
                <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #EEEAE9; color: #4A3F41;">
                  {{ $pn->Trang_thai }}
                </span>
                <div style="color: #6B5559; font-size: 11px; margin-top: 2px;">{{ $pn->Ngay_lap ? \Carbon\Carbon::parse($pn->Ngay_lap)->format('d/m/Y') : '' }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>

</div>
@endsection
