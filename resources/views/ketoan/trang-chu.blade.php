@extends('layouts.app')

@section('title', 'Trang chủ Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  {{-- Tiêu đề trang --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0 0 4px 0;">Trang chủ</h1>
      <p style="font-size: 14px; color: #6B5559; margin: 0;">
        Tổng quan tài chính kho hàng, doanh thu và phê duyệt đề xuất mua hàng.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="{{ route('ke-toan.de-xuat.index', ['tab' => 'cho-duyet']) }}"
         style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;"
         onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
        Duyệt đề xuất
      </a>
    </div>
  </div>

  {{-- Phần 1: Việc cần xử lý --}}
  <div>
    <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 12px 0;">
      Việc cần xử lý
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
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

  {{-- Phần 2: Sáu ô số liệu tháng (theo 07-nguon-du-lieu.md mục 4) --}}
  <div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
      <h2 style="font-size: 15px; font-weight: 600; color: #6E1423; letter-spacing: 0.05em; text-transform: uppercase; margin: 0;">
        Chỉ số tài chính tháng {{ now()->format('m/Y') }}
      </h2>
      <span style="font-size: 12px; color: #6B5559;">Áp dụng toàn công ty (2 kho)</span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
      
      {{-- 1. Giá trị tồn kho --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Giá trị tồn kho hiện tại</div>
        <div style="font-size: 24px; font-weight: 700; color: #241417;">
          {{ number_format($giaTriTonKho, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Tính theo giá vốn bình quân</div>
      </div>

      {{-- 2. Doanh thu tháng --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Doanh thu tháng này</div>
        <div style="font-size: 24px; font-weight: 700; color: #1E5B36;">
          {{ number_format($doanhThuThang, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Từ các đơn hàng hoàn thành</div>
      </div>

      {{-- 3. Giá vốn hàng bán --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Giá vốn hàng bán (COGS)</div>
        <div style="font-size: 24px; font-weight: 700; color: #241417;">
          {{ number_format($giaVonThang, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Giá vốn thực xuất kho</div>
      </div>

      {{-- 4. Lãi gộp --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Lãi gộp ước tính</div>
        <div style="font-size: 24px; font-weight: 700; color: {{ $laiGopThang >= 0 ? '#1E5B36' : '#B42318' }};">
          {{ number_format($laiGopThang, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Doanh thu trừ giá vốn</div>
      </div>

      {{-- 5. Giá trị nhập mua --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Giá trị nhập mua tháng này</div>
        <div style="font-size: 24px; font-weight: 700; color: #241417;">
          {{ number_format($giaTriNhapMua, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Tổng tiền nhập từ nhà cung cấp</div>
      </div>

      {{-- 6. Hàng mất / hỏng --}}
      <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
        <div style="font-size: 13px; color: #6B5559; margin-bottom: 6px;">Hao hụt & Hàng hỏng tháng này</div>
        <div style="font-size: 24px; font-weight: 700; color: {{ $hangMatHongThang > 0 ? '#B42318' : '#241417' }};">
          {{ number_format($hangMatHongThang, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6B5559;">đ</span>
        </div>
        <div style="font-size: 12px; color: #6B5559; margin-top: 6px;">Hỏng khi đổi lô & mất do hoàn giao</div>
      </div>

    </div>
  </div>

  {{-- Phần 3: Đề xuất nhập kho cần duyệt & mới nhất --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0;">Đề xuất nhập kho</h3>
      <a href="{{ route('ke-toan.de-xuat.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 600;">Xem tất cả →</a>
    </div>
    @if($deXuatGanDay->isEmpty())
      <div style="padding: 24px; text-align: center; color: #6B5559; font-size: 14px;">Chưa có đề xuất nhập kho nào.</div>
    @else
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #F6ECEA; text-align: left;">
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Mã đơn</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Kho</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Nhà cung cấp</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Tổng tiền</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Trạng thái</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deXuatGanDay as $dx)
              <tr style="border-top: 1px solid #F0E6E3;">
                <td style="padding: 10px 14px; font-weight: 600;">{{ $dx->Ma_don_dat_hang }}</td>
                <td style="padding: 10px 14px; color: #6B5559;">{{ $dx->kho->Ten_kho ?? '—' }}</td>
                <td style="padding: 10px 14px;">{{ $dx->nhaCungCap->Ten_ncc ?? '—' }}</td>
                <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                  {{ number_format($dx->Tong_gia_tri ?? 0, 0, ',', '.') }} đ
                </td>
                <td style="padding: 10px 14px;">
                  <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; 
                               {{ $dx->Trang_thai === 'Chờ phê duyệt' ? 'background: #FCEFD9; color: #8A4B08;' : 'background: #EADFDC; color: #5E1320;' }}">
                    {{ $dx->Trang_thai }}
                  </span>
                </td>
                <td style="padding: 10px 14px;">
                  <a href="{{ route('ke-toan.de-xuat.show', $dx->Ma_don_dat_hang) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                    {{ $dx->Trang_thai === 'Chờ phê duyệt' ? 'Duyệt' : 'Xem' }}
                  </a>
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
