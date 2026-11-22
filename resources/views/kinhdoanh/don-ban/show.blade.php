@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng ' . $donBan->Ma_don_hang_ban . ' – Soul Wine')

@section('content')
<div style="padding: 24px 32px; max-width: 1100px; margin: 0 auto;">

  {{-- Header --}}
  <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <a href="{{ route('kinh-doanh.don-ban.index') }}" style="color: #8B1E2D; text-decoration: none;">Đơn hàng bán</a>
        &nbsp;/&nbsp; <strong style="color: #241417;">{{ $donBan->Ma_don_hang_ban }}</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">
        {{ $donBan->Ma_don_hang_ban }}
        @php
          $badgeStyle = match($donBan->Trang_thai) {
            'Đang chuẩn bị hàng' => 'background:#EEF2FF;color:#3730A3;',
            'Đang giao'           => 'background:#FEF3C7;color:#92400E;',
            'Hoàn thành'          => 'background:#DEF7EC;color:#03543F;',
            'Giao thất bại'       => 'background:#FDE8E8;color:#9B1C1C;',
            'Đã hủy'              => 'background:#F3F4F6;color:#6B7280;',
            default               => 'background:#F3F4F6;color:#374151;',
          };
        @endphp
        <span style="font-size: 13px; font-weight: 600; padding: 3px 10px; border-radius: 6px; vertical-align: middle; {{ $badgeStyle }}">{{ $donBan->Trang_thai }}</span>
        @if($thieu)
          <span style="font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 6px; vertical-align: middle; background: #FDE8E8; color: #9B1C1C; margin-left: 6px;">⚠ Thiếu tồn</span>
        @endif
      </h1>
    </div>

    @if($donBan->Trang_thai === 'Đang chuẩn bị hàng')
    <button onclick="document.getElementById('modal-huy').style.display='flex'"
      style="height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #F8B4B4; background: #FDE8E8; color: #9B1C1C; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
      Hủy đơn hàng
    </button>
    @endif
  </div>

  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px; margin-bottom: 16px;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">{{ session('error') }}</div>
  @endif

  {{-- Giao thất bại banner --}}
  @if($donBan->Trang_thai === 'Giao thất bại' && $px?->Ly_do_giao_that_bai)
  <div style="padding: 14px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">
    <strong>Giao hàng thất bại:</strong> {{ $px->Ly_do_giao_that_bai }}
  </div>
  @endif

  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">

      {{-- Dòng sản phẩm --}}
      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #EDE0DC;">
          <h2 style="font-size: 15px; font-weight: 700; color: #5E1320; margin: 0;">Sản phẩm đặt mua</h2>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #F8F0EE;">
              <th style="padding: 10px 16px; font-weight: 600; color: #5E1320; text-align: left;">Sản phẩm</th>
              <th style="padding: 10px 16px; font-weight: 600; color: #5E1320; text-align: center;">SL</th>
              <th style="padding: 10px 16px; font-weight: 600; color: #5E1320; text-align: center;">CK (%)</th>
              <th style="padding: 10px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đơn giá</th>
              <th style="padding: 10px 16px; font-weight: 600; color: #5E1320; text-align: right;">Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            @foreach($donBan->chiTiets as $ct)
            <tr style="border-top: 1px solid #F3EAE7;">
              <td style="padding: 10px 16px;">
                <div style="font-weight: 600;">{{ $ct->sanPham?->Ten_san_pham ?? $ct->Ma_san_pham }}</div>
                <div style="font-size: 12px; color: #6B5559;">{{ $ct->Ma_san_pham }}</div>
              </td>
              <td style="padding: 10px 16px; text-align: center; font-weight: 600;">{{ $ct->So_luong }}</td>
              <td style="padding: 10px 16px; text-align: center; color: #6B5559;">{{ $ct->Chiet_khau > 0 ? number_format($ct->Chiet_khau, 1) . '%' : '—' }}</td>
              <td style="padding: 10px 16px; text-align: right;">{{ number_format($ct->Don_gia_ban) }} đ</td>
              <td style="padding: 10px 16px; text-align: right; font-weight: 600; color: #8B1E2D;">{{ number_format($ct->So_luong * $ct->Don_gia_ban) }} đ</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background: #FDFAF8; border-top: 2px solid #EDE0DC;">
              <td colspan="4" style="padding: 12px 16px; text-align: right; font-weight: 700;">Tổng tiền hàng</td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 700; font-size: 16px; color: #8B1E2D;">{{ number_format($donBan->Tong_tien_hang) }} đ</td>
            </tr>
            <tr style="background: #FDFAF8;">
              <td colspan="4" style="padding: 4px 16px; text-align: right; color: #6B5559; font-size: 13px;">Thuế GTGT 8% (tham khảo)</td>
              <td style="padding: 4px 16px; text-align: right; color: #6B5559; font-size: 13px;">{{ number_format(round($donBan->Tong_tien_hang * 0.08)) }} đ</td>
            </tr>
            <tr style="background: #FDFAF8;">
              <td colspan="4" style="padding: 4px 16px 12px; text-align: right; font-weight: 700; color: #241417;">Tổng thanh toán (tham khảo)</td>
              <td style="padding: 4px 16px 12px; text-align: right; font-weight: 700; color: #241417;">{{ number_format(round($donBan->Tong_tien_hang * 1.08)) }} đ</td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Thông tin giao hàng --}}
      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 15px; font-weight: 700; color: #5E1320; margin: 0;">Thông tin giao hàng</h2>
          @if($donBan->Trang_thai === 'Đang chuẩn bị hàng')
            <button onclick="document.getElementById('form-giao-hang').style.display = document.getElementById('form-giao-hang').style.display === 'none' ? 'block' : 'none'"
              style="height: 32px; padding: 0 14px; border-radius: 6px; border: 1px solid #D6C8C5; background: #fff; color: #6B5559; font: inherit; font-size: 13px; cursor: pointer;">
              Chỉnh sửa
            </button>
          @endif
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px;">
          <div><span style="color: #6B5559;">Người nhận:</span> <strong>{{ $donBan->Ten_nguoi_nhan }}</strong></div>
          <div><span style="color: #6B5559;">SĐT:</span> <strong>{{ $donBan->Sdt_nguoi_nhan }}</strong></div>
          <div style="grid-column: span 2;"><span style="color: #6B5559;">Địa chỉ:</span> <strong>{{ $donBan->Dia_chi_giao_hang }}</strong></div>
        </div>

        @if($px)
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #F3EAE7; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px;">
          <div><span style="color: #6B5559;">Phiếu xuất:</span> <strong>{{ $px->Ma_phieu_xuat }}</strong></div>
          <div><span style="color: #6B5559;">Trạng thái:</span> <strong>{{ $px->Trang_thai_giao_hang }}</strong></div>
          @if($px->Don_vi_van_chuyen)
          <div><span style="color: #6B5559;">Vận chuyển:</span> <strong>{{ $px->Don_vi_van_chuyen }}</strong></div>
          <div><span style="color: #6B5559;">Vận đơn:</span> <strong>{{ $px->Ma_van_don ?? '—' }}</strong></div>
          @endif
          @if($px->Thoi_diem_xuat_kho)
          <div><span style="color: #6B5559;">Xuất kho lúc:</span> <strong>{{ \Carbon\Carbon::parse($px->Thoi_diem_xuat_kho)->format('d/m/Y H:i') }}</strong></div>
          @endif
          @if($px->Thoi_diem_ket_qua_giao)
          <div><span style="color: #6B5559;">Kết quả giao:</span> <strong>{{ \Carbon\Carbon::parse($px->Thoi_diem_ket_qua_giao)->format('d/m/Y H:i') }}</strong></div>
          @endif
        </div>
        @endif

        {{-- Form chỉnh sửa giao hàng --}}
        <div id="form-giao-hang" style="display: none; margin-top: 20px; padding-top: 16px; border-top: 1px solid #F3EAE7;">
          <form method="POST" action="{{ route('kinh-doanh.don-ban.update-giao-hang', $donBan->Ma_don_hang_ban) }}">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Tên người nhận *</label>
                <input type="text" name="ten_nguoi_nhan" value="{{ $donBan->Ten_nguoi_nhan }}"
                  style="width: 100%; height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; box-sizing: border-box;">
              </div>
              <div>
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">SĐT người nhận *</label>
                <input type="text" name="sdt_nguoi_nhan" value="{{ $donBan->Sdt_nguoi_nhan }}"
                  style="width: 100%; height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; box-sizing: border-box;">
              </div>
              <div style="grid-column: span 2;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Địa chỉ giao hàng *</label>
                <input type="text" name="dia_chi_giao_hang" value="{{ $donBan->Dia_chi_giao_hang }}"
                  style="width: 100%; height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; box-sizing: border-box;">
              </div>
            </div>
            <button type="submit" style="height: 36px; padding: 0 20px; border-radius: 6px; border: 0; background: #8B1E2D; color: #fff; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Lưu thông tin giao hàng</button>
          </form>
        </div>
      </div>

    </div>

    {{-- Sidebar thông tin --}}
    <div style="display: flex; flex-direction: column; gap: 16px;">

      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px;">
        <h2 style="font-size: 14px; font-weight: 700; color: #5E1320; margin: 0 0 14px;">Thông tin đơn hàng</h2>
        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 8px;">
            <span style="color: #6B5559;">Kho xuất</span>
            <span style="font-weight: 600; text-align: right;">{{ $donBan->kho?->Ten_kho ?? $donBan->Ma_kho }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; gap: 8px;">
            <span style="color: #6B5559;">Ngày xác nhận</span>
            <span style="font-weight: 600;">{{ $donBan->Thoi_diem_xac_nhan?->format('d/m/Y H:i') }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; gap: 8px;">
            <span style="color: #6B5559;">Người lập</span>
            <span style="font-weight: 600;">{{ $donBan->nguoiLap?->Ho_ten }}</span>
          </div>
          @if($donBan->Thoi_diem_huy)
          <div style="display: flex; justify-content: space-between; gap: 8px;">
            <span style="color: #6B5559;">Ngày hủy</span>
            <span style="font-weight: 600; color: #9B1C1C;">{{ \Carbon\Carbon::parse($donBan->Thoi_diem_huy)->format('d/m/Y H:i') }}</span>
          </div>
          @endif
          @if($donBan->Ly_do_huy)
          <div>
            <div style="color: #6B5559; margin-bottom: 2px;">Lý do hủy</div>
            <div style="font-weight: 600; color: #9B1C1C;">{{ $donBan->Ly_do_huy }}</div>
          </div>
          @endif
        </div>
      </div>

      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px;">
        <h2 style="font-size: 14px; font-weight: 700; color: #5E1320; margin: 0 0 14px;">Khách hàng</h2>
        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
          <div style="font-weight: 700; font-size: 14px;">{{ $donBan->khachHang?->Ten_khach_hang }}</div>
          <div style="color: #6B5559;">{{ $donBan->khachHang?->Ma_khach_hang }}</div>
          @if($donBan->khachHang?->So_dien_thoai)
          <div>📞 {{ $donBan->khachHang->So_dien_thoai }}</div>
          @endif
          @if($donBan->khachHang?->Email)
          <div>✉ {{ $donBan->khachHang->Email }}</div>
          @endif
        </div>
      </div>

    </div>
  </div>

</div>

{{-- Modal hủy đơn --}}
<div id="modal-huy" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #fff; border-radius: 12px; padding: 28px; width: 480px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <h3 style="font-size: 18px; font-weight: 700; color: #9B1C1C; margin: 0 0 8px;">Hủy đơn hàng {{ $donBan->Ma_don_hang_ban }}</h3>
    <p style="font-size: 14px; color: #6B5559; margin: 0 0 20px;">Hành động này không thể hoàn tác. Phiếu xuất sẽ chuyển sang "Đã hủy" và hàng đang giữ sẽ được giải phóng.</p>

    <form method="POST" action="{{ route('kinh-doanh.don-ban.huy', $donBan->Ma_don_hang_ban) }}">
      @csrf
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">Lý do hủy *</label>
        <textarea name="ly_do_huy" rows="3" placeholder="Nhập lý do hủy đơn..."
          style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; resize: vertical; box-sizing: border-box;"></textarea>
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-huy').style.display='none'"
          style="height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; color: #6B5559; font: inherit; font-size: 14px; cursor: pointer;">
          Đóng
        </button>
        <button type="submit" style="height: 40px; padding: 0 24px; border-radius: 8px; border: 0; background: #9B1C1C; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer;">
          Xác nhận hủy đơn
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

