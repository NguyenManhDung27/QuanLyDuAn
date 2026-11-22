@extends('layouts.app')

@section('title', 'Trang chủ Quản lý kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  {{-- Tiêu đề trang --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0 0 4px 0;">Trang chủ</h1>
      <p style="font-size: 14px; color: #6B5559; margin: 0;">
        Tổng quan hoạt động và các công việc cần xử lý tại <strong style="color: #241417;">{{ $kho->Ten_kho ?? 'Kho' }}</strong>.
      </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="{{ route('kho.de-xuat.create') }}"
         style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;">
        + Tạo đề xuất
      </a>
      <a href="{{ route('kho.kiem-ke.create') }}"
         style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;"
         onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
        + Tạo phiếu kiểm kê
      </a>
    </div>
  </div>

  {{-- Phần 1: Việc cần xử lý (Action Cards) --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 12px 0;">
      Việc cần xử lý
    </h2>
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
  </div>

  {{-- Phần 2: Thống kê tồn kho tổng quan --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 12px 0;">
      Tình trạng kho hàng
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Tổng sản phẩm đang kinh doanh</div>
        <div style="font-size: 26px; font-weight: 700; color: #241417;">{{ number_format($tongSanPham) }}</div>
      </div>
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Tổng tồn thực tại kho</div>
        <div style="font-size: 26px; font-weight: 700; color: #1E5B36;">{{ number_format($tongTonThuc) }} <span style="font-size: 15px; font-weight: 400; color: #6B5559;">chai</span></div>
      </div>
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Lô sắp hết hạn (30 ngày)</div>
        <div style="font-size: 26px; font-weight: 700; color: {{ $soLoSapHetHan > 0 ? '#B42318' : '#241417' }};">
          {{ number_format($soLoSapHetHan) }} <span style="font-size: 15px; font-weight: 400; color: #6B5559;">lô</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Phần 3: Danh sách hoạt động gần đây (2 cột) --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
    
    {{-- Đơn đặt hàng & đề xuất gần đây --}}
    <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Đơn đặt hàng & Đề xuất mới</h3>
        <a href="{{ route('kho.don-dat-hang.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
      </div>
      @if($donGanDay->isEmpty())
        <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Chưa có đơn đặt hàng nào.</div>
      @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
          @foreach($donGanDay as $d)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #FAF5F3; border-radius: 8px; font-size: 13px;">
              <div>
                <a href="{{ route('kho.don-dat-hang.index') }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                  {{ $d->Ma_don_dat_hang }}
                </a>
                <div style="color: #6B5559; font-size: 12px; margin-top: 2px;">{{ $d->nhaCungCap->Ten_ncc ?? 'NCC' }}</div>
              </div>
              <div style="text-align: right;">
                <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #EADFDC; color: #5E1320;">
                  {{ $d->Trang_thai }}
                </span>
                <div style="color: #6B5559; font-size: 11px; margin-top: 2px;">{{ $d->Ngay_lap ? \Carbon\Carbon::parse($d->Ngay_lap)->format('d/m/Y') : '' }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Phiếu kiểm kê gần đây --}}
    <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Phiếu kiểm kê gần đây</h3>
        <a href="{{ route('kho.kiem-ke.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
      </div>
      @if($kiemKeGanDay->isEmpty())
        <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Chưa có phiếu kiểm kê nào.</div>
      @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
          @foreach($kiemKeGanDay as $kk)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #FAF5F3; border-radius: 8px; font-size: 13px;">
              <div>
                <a href="{{ route('kho.kiem-ke.show', $kk->Ma_phieu_kiem_ke) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                  {{ $kk->Ma_phieu_kiem_ke }}
                </a>
                <div style="color: #6B5559; font-size: 12px; margin-top: 2px;">{{ $kk->nguoiKiem->Ho_ten ?? 'Chưa giao' }}</div>
              </div>
              <div style="text-align: right;">
                <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #EADFDC; color: #5E1320;">
                  {{ $kk->Trang_thai }}
                </span>
                <div style="color: #6B5559; font-size: 11px; margin-top: 2px;">{{ $kk->Thoi_diem_tao ? \Carbon\Carbon::parse($kk->Thoi_diem_tao)->format('d/m/Y H:i') : '' }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>

</div>
@endsection
