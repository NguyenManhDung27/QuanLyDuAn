@extends('layouts.app')

@section('title', 'Chi tiết đề xuất nhập kho ' . $don->Ma_don_dat_hang . ' – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Back Link, Title, Badges, Action Buttons --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <a href="{{ route('ke-toan.de-xuat.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>Đề xuất nhập kho</span>
      </a>

      <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Đề xuất nhập kho {{ $don->Ma_don_dat_hang }}</h1>
        @php
          $badgeStyle = match($don->Trang_thai) {
            'Chờ phê duyệt' => 'background: #FCEFD9; color: #8A4B08;',
            'Từ chối' => 'background: #F6E1E1; color: #8B1E2D;',
            'Đóng thiếu' => 'background: #E0F2FE; color: #0369A1;',
            'Đơn đã hủy' => 'background: #F3F4F6; color: #6B7280;',
            default => 'background: #E5F2EA; color: #1E5B36;',
          };
        @endphp
        <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; {{ $badgeStyle }}">
          {{ $don->Trang_thai }}
        </span>
      </div>
    </div>

    {{-- Actions for Kế toán when 'Chờ phê duyệt' --}}
    @if($don->Trang_thai === 'Chờ phê duyệt')
      <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <button type="button" onclick="document.getElementById('rejectModal').style.display='flex';"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 0; background: #F6E1E1; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;"
                onmouseover="this.style.background='#F0CCCC';" onmouseout="this.style.background='#F6E1E1';">
          Từ chối
        </button>

        <button type="button" onclick="document.getElementById('approveModal').style.display='flex';"
                style="height: 44px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.08);"
                onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
          Duyệt
        </button>
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

  @if($errors->any())
    <div style="padding: 14px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px;">
      <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Card thông tin chung --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; font-size: 14px;">
      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Kho nhận</span>
        <span style="font-weight: 600; color: #241417;">{{ $don->kho->Ten_kho ?? $don->Ma_kho }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Nhà cung cấp</span>
        <span style="font-weight: 600; color: #241417;">{{ $don->nhaCungCap->Ten_ncc ?? $don->Ma_ncc }} · {{ $don->nhaCungCap->Quoc_gia ?? '—' }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Người lập · Ngày lập</span>
        <span style="font-weight: 500; color: #241417;">{{ $don->nguoiLap->Ho_ten ?? $don->Ma_nguoi_lap }} · {{ $don->Ngay_lap ? date('d/m/Y', strtotime($don->Ngay_lap)) : '—' }}</span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="font-size: 12px; color: #6B5559;">Dự kiến nhận đủ</span>
        <span style="font-weight: 600; color: #241417;">{{ $don->Ngay_du_kien_nhan ? date('d/m/Y', strtotime($don->Ngay_du_kien_nhan)) : '—' }}</span>
      </div>

      @if($don->Ma_nguoi_duyet)
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-size: 12px; color: #6B5559;">Người duyệt · Thời điểm duyệt</span>
          <span style="font-weight: 500; color: #241417;">{{ $don->nguoiDuyet->Ho_ten ?? $don->Ma_nguoi_duyet }} · {{ $don->Thoi_diem_duyet ? date('d/m/Y H:i', strtotime($don->Thoi_diem_duyet)) : '—' }}</span>
        </div>
      @endif
    </div>

    @if($don->Ghi_chu_qlk)
      <div style="font-size: 14px; padding-top: 12px; border-top: 1px solid #F0E6E3; color: #241417;">
        <span style="color: #6B5559;">Ghi chú của Quản lý kho:</span> {{ $don->Ghi_chu_qlk }}
      </div>
    @endif

    @if($don->Ghi_chu_phe_duyet)
      <div style="font-size: 14px; padding-top: 8px; color: #241417;">
        <span style="color: #6B5559;">Ghi chú phê duyệt:</span> {{ $don->Ghi_chu_phe_duyet }}
      </div>
    @endif
  </section>

  {{-- Card danh sách sản phẩm đề xuất --}}
  <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Sản phẩm đề xuất mua</h2>

    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">SL đặt</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đơn giá mua</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Thành tiền</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Giá vốn BQ hiện tại</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Tồn khả dụng kho nhận</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Bán 90 ngày qua</th>
          </tr>
        </thead>
        <tbody>
          @foreach($don->chiTiets as $ct)
            @php
              $sp = $ct->sanPham;
              $giaVon = (float) ($sp->Gia_von_binh_quan ?? 0);
              $donGia = (float) $ct->Don_gia_mua;
              $isCaoHon = ($giaVon > 0 && $donGia > $giaVon);
              $pctCaoHon = $isCaoHon ? round((($donGia - $giaVon) / $giaVon) * 100, 1) : 0;
              $tonKhaDung = $tonKhaDungMap[$ct->Ma_san_pham][$don->Ma_kho] ?? 0;
              $ban90Ngay = $ban90NgayMap[$ct->Ma_san_pham] ?? 0;
            @endphp
            <tr style="border-top: 1px solid #F0E6E3; {{ $isCaoHon ? 'background: #FDF8F2;' : '' }}">
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                <div>{{ $sp->Ten_san_pham ?? $ct->Ma_san_pham }}</div>
                <div style="font-size: 12px; color: #6B5559;">
                  {{ $sp->Ma_san_pham }} · {{ $sp->Nien_vu ?? 'NV' }} · {{ $sp->Dung_tich_ml ?? '750' }}ml · {{ $sp->Nong_do_con ? $sp->Nong_do_con . '%' : '—' }}
                </div>
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">
                {{ number_format($ct->So_luong_dat) }}
              </td>
              <td style="padding: 12px 16px; text-align: right;">
                <div style="{{ $isCaoHon ? 'font-weight: 600; color: #8A4B08;' : 'color: #241417;' }}">
                  {{ number_format($donGia, 0, ',', '.') }}
                </div>
                @if($isCaoHon)
                  <div style="font-size: 11px; font-weight: 500; color: #B45309; margin-top: 2px;">
                    Cao hơn giá vốn bình quân hiện tại {{ $pctCaoHon }}%
                  </div>
                @endif
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">
                {{ number_format($ct->Thanh_tien, 0, ',', '.') }}
              </td>
              <td style="padding: 12px 16px; text-align: right; color: #6B5559;">
                {{ $giaVon > 0 ? number_format($giaVon, 0, ',', '.') : '—' }}
              </td>
              <td style="padding: 12px 16px; text-align: right; color: #241417;">
                {{ number_format($tonKhaDung) }}
              </td>
              <td style="padding: 12px 16px; text-align: right; color: #241417;">
                {{ number_format($ban90Ngay) }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="display: flex; justify-content: flex-end; align-items: baseline; gap: 12px; padding-top: 12px; font-size: 15px; border-top: 1px solid #F0E6E3;">
      <span style="color: #6B5559;">Tổng giá trị (chưa thuế):</span>
      <span style="font-size: 18px; font-weight: 700; color: #6E1423;">{{ number_format($don->Tong_tien, 0, ',', '.') }} VNĐ</span>
    </div>

    <p style="margin: 0; font-size: 13px; color: #6B5559; line-height: 1.5;">
      * Giá vốn bình quân, tồn khả dụng và số bán 90 ngày (tổng chai giao thành công tại kho nhận, trừ hàng trả) chỉ để tham khảo khi duyệt, hệ thống tính tại thời điểm xem, không lưu vào đề xuất.
    </p>
  </section>

  {{-- Card Lần duyệt trước (nếu từng bị từ chối) --}}
  @if($don->Ly_do_tu_choi)
    <section style="display: flex; flex-direction: column; gap: 10px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
      <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #8B1E2D;">Lần duyệt trước</h2>
      <div style="font-size: 14px; color: #3B2A2D; line-height: 1.6;">
        <span style="font-weight: 600; color: #8B1E2D;">Từ chối</span>
        @if($don->Ma_nguoi_duyet)
          · <span style="font-weight: 500;">{{ $don->nguoiDuyet->Ho_ten ?? $don->Ma_nguoi_duyet }}</span>
        @endif
        @if($don->Thoi_diem_duyet)
          · <span>{{ date('d/m/Y H:i', strtotime($don->Thoi_diem_duyet)) }}</span>
        @endif
        <div style="margin-top: 4px; padding: 10px 14px; background: #FDF2F2; border-radius: 6px; border-left: 3px solid #8B1E2D; color: #771D1D;">
          <strong>Lý do từ chối:</strong> {{ $don->Ly_do_tu_choi }}
        </div>
      </div>
    </section>
  @endif
</div>

{{-- Modal Từ chối đề xuất --}}
@if($don->Trang_thai === 'Chờ phê duyệt')
  <div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; width: 480px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 16px;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #8B1E2D;">Từ chối đề xuất nhập kho</h3>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">
        Từ chối đề xuất? Quản lý kho sẽ sửa và gửi lại, hoặc hủy đề xuất.
      </p>

      <form method="POST" action="{{ route('ke-toan.de-xuat.tu-choi', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; color: #241417;">
          <span>Lý do từ chối <strong style="color: #8B1E2D;">*</strong></span>
          <textarea name="ly_do_tu_choi" rows="3" required placeholder="Nhập lý do từ chối (bắt buộc)..."
                    style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
        </label>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px;">
          <button type="button" onclick="document.getElementById('rejectModal').style.display='none';"
                  style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; font: inherit; font-size: 14px; cursor: pointer;">
            Quay lại
          </button>
          <button type="submit"
                  style="height: 40px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Từ chối
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Duyệt đề xuất --}}
  <div id="approveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; width: 480px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 16px;">
      <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #1E5B36;">Duyệt đề xuất nhập kho</h3>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">
        Duyệt đề xuất? Đề xuất chuyển thành đơn đặt hàng trạng thái <strong>Chưa gửi NCC</strong>, Quản lý kho sẽ gửi cho nhà cung cấp.
      </p>

      <form method="POST" action="{{ route('ke-toan.de-xuat.duyet', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; color: #241417;">
          <span>Ghi chú phê duyệt (không bắt buộc)</span>
          <textarea name="ghi_chu_phe_duyet" rows="3" placeholder="Ghi chú thêm nếu có..."
                    style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
        </label>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px;">
          <button type="button" onclick="document.getElementById('approveModal').style.display='none';"
                  style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; font: inherit; font-size: 14px; cursor: pointer;">
            Quay lại
          </button>
          <button type="submit"
                  style="height: 40px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Duyệt
          </button>
        </div>
      </form>
    </div>
  </div>
@endif
@endsection

