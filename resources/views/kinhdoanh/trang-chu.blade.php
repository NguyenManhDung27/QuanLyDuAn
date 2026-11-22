@extends('layouts.app')

@section('title', 'Trang chủ Kinh doanh – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  {{-- Tiêu đề trang --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0 0 4px 0;">Trang chủ</h1>
      <p style="font-size: 14px; color: #6B5559; margin: 0;">
        Tổng quan hoạt động bán hàng, quản lý đơn hàng và khách hàng.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="{{ route('kinh-doanh.don-ban.create') }}"
         style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;"
         onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
        + Lập đơn bán
      </a>
    </div>
  </div>

  {{-- Phần 1: Việc cần xử lý --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 12px 0;">
      Việc cần xử lý
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
      @foreach($cards as $c)
        <a href="{{ $c['href'] }}"
           style="display: flex; flex-direction: column; gap: 10px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; color: #241417; text-decoration: none; min-height: 128px; transition: transform 0.15s ease, box-shadow 0.15s ease;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(110,20,35,0.08)';"
           onmouseout="this.style.transform='none';this.style.boxShadow='none';">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
            <span style="font-size: 15px; font-weight: 600; color: #241417;">{{ $c['label'] }}</span>
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

  {{-- Phần 2: Thống kê kinh doanh --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 12px 0;">
      Hiệu quả bán hàng tháng {{ now()->format('m/Y') }}
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Tổng khách hàng đang giao dịch</div>
        <div style="font-size: 26px; font-weight: 700; color: #241417;">{{ number_format($tongKhachHang) }}</div>
      </div>
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Đơn hoàn thành trong tháng</div>
        <div style="font-size: 26px; font-weight: 700; color: #1E5B36;">{{ number_format($donThanhCongThang) }} <span style="font-size: 15px; font-weight: 400; color: #6B5559;">đơn</span></div>
      </div>
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Doanh số hoàn thành tháng</div>
        <div style="font-size: 26px; font-weight: 700; color: #8B1E2D;">
          {{ number_format($doanhThuThang, 0, ',', '.') }} <span style="font-size: 15px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Phần 3: Đơn hàng mới nhất --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Đơn hàng bán gần đây</h3>
      <a href="{{ route('kinh-doanh.don-ban.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
    </div>
    @if($donBanGanDay->isEmpty())
      <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Chưa có đơn hàng bán nào.</div>
    @else
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #F6ECEA; text-align: left;">
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Mã đơn</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Khách hàng</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Kho xuất</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Tổng thanh toán</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Trạng thái</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @foreach($donBanGanDay as $db)
              <tr style="border-top: 1px solid #F0E6E3;">
                <td style="padding: 10px 14px; font-weight: 600;">{{ $db->Ma_don_hang_ban }}</td>
                <td style="padding: 10px 14px;">{{ $db->khachHang->Ten_khach_hang ?? 'Khách lẻ' }}</td>
                <td style="padding: 10px 14px; color: #6B5559;">{{ $db->kho->Ten_kho ?? '—' }}</td>
                <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                  {{ number_format($db->Tong_tien_hang ?? 0, 0, ',', '.') }} đ
                </td>
                <td style="padding: 10px 14px;">
                  @php
                    $bBg = match($db->Trang_thai) {
                      'Hoàn thành' => 'background: #E5F2EA; color: #1E5B36;',
                      'Đang chuẩn bị hàng' => 'background: #FCEFD9; color: #8A4B08;',
                      'Đang giao' => 'background: #E3EAFB; color: #1D3F9E;',
                      'Giao thất bại' => 'background: #F6E1E1; color: #8B1E2D;',
                      'Đã hủy' => 'background: #EEEAE9; color: #6B5559;',
                      default => 'background: #EADFDC; color: #5E1320;',
                    };
                  @endphp
                  <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; {{ $bBg }}">
                    {{ $db->Trang_thai }}
                  </span>
                </td>
                <td style="padding: 10px 14px;">
                  <a href="{{ route('kinh-doanh.don-ban.show', $db->Ma_don_hang_ban) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">Xem</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection
