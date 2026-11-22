@extends('layouts.app')

@section('title', 'Phiếu xuất ' . $phieuXuat->Ma_phieu_xuat . ' – Soul Wine')

@section('content')
<div style="padding: 24px 32px; max-width: 1200px; margin: 0 auto;">

  {{-- Header --}}
  <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <a href="{{ route('nv-kho.phieu-xuat.index') }}" style="color: #8B1E2D; text-decoration: none;">← Phiếu xuất kho</a>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Phiếu xuất {{ $phieuXuat->Ma_phieu_xuat }}</h1>
        @php
          $badgeStyle = match($phieuXuat->Trang_thai_giao_hang) {
            'Đang chuẩn bị hàng' => 'background:#FCEFD9;color:#8A4B08;',
            'Đang giao'          => 'background:#E3EAFB;color:#1D3F9E;',
            'Giao thành công'    => 'background:#E5F2EA;color:#1E5B36;',
            'Giao hàng thất bại' => 'background:#F6E1E1;color:#8B1E2D;',
            'Đã hủy'             => 'background:#EEEAE9;color:#6B5559;',
            default              => 'background:#F3F4F6;color:#374151;',
          };
        @endphp
        <span style="font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 999px; {{ $badgeStyle }}">{{ $phieuXuat->Trang_thai_giao_hang }}</span>
        @if($thieu)
          <span style="font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: #FBEDED; color: #B42318;">⚠ Thiếu tồn</span>
        @endif
        @if($phieuXuat->Trang_thai_hoan_hang === 'Chờ xử lý')
          <span style="font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; border: 1px solid #B42318; color: #B42318;">Chờ xử lý hoàn</span>
        @endif
      </div>
    </div>

    {{-- Action buttons --}}
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      @if($phieuXuat->Trang_thai_giao_hang === 'Đang chuẩn bị hàng')
        <button type="button" onclick="openModalXuatKho()"
          style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;"
          onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
          Xác nhận xuất kho
        </button>
      @elseif($phieuXuat->Trang_thai_giao_hang === 'Đang giao')
        <button type="button" onclick="openModalEditVanChuyen()"
          style="height: 42px; padding: 0 16px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Sửa thông tin vận chuyển
        </button>
        <button type="button" onclick="openModalGiaoThatBai()"
          style="height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #F6E1E1; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Giao thất bại
        </button>
        <button type="button" onclick="openModalGiaoThanhCong()"
          style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Giao thành công
        </button>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px; margin-bottom: 16px;">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">{{ session('error') }}</div>
  @endif

  {{-- Alert cảnh báo thiếu tồn --}}
  @if($thieu)
    <div role="alert" style="padding: 14px 18px; border-radius: 10px; background: #FBEDED; border: 1px solid #E8C4C4; font-size: 14px; color: #3B2A2D; margin-bottom: 16px;">
      <b style="color: #B42318;">Thiếu tồn:</b> có lô phân bổ không còn đủ tồn thực tế sau khi trừ hàng hỏng hoặc kiểm kê. Vui lòng bấm <b>Đổi lô</b> sang lô khác; nếu không còn lô nào đủ, báo Kinh doanh hủy đơn.
    </div>
  @endif

  {{-- Alert giao thất bại --}}
  @if($phieuXuat->Trang_thai_giao_hang === 'Giao hàng thất bại')
    <div style="padding: 14px 18px; border-radius: 10px; background: #FBEDED; border: 1px solid #E8C4C4; font-size: 14px; color: #3B2A2D; margin-bottom: 16px;">
      <b style="color: #8B1E2D;">Giao thất bại</b>
      @if($phieuXuat->Thoi_diem_ket_qua_giao)
        · {{ \Carbon\Carbon::parse($phieuXuat->Thoi_diem_ket_qua_giao)->format('d/m/Y H:i') }}
      @endif
      · Lý do: <strong>{{ $phieuXuat->Ly_do_giao_that_bai }}</strong>.
      Khi hàng hoàn về đến kho, xử lý ở <a href="{{ route('nv-kho.nhap-kho.index', ['loai' => 'Hoàn giao thất bại']) }}" style="font-weight: 600; color: #8B1E2D;">Nhập kho → Hoàn giao thất bại</a>.
    </div>
  @endif

  {{-- Khối thông tin chung --}}
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; margin-bottom: 20px;">
    <div>
      <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Đơn bán</span>
      <span style="font-weight: 600; color: #241417;">{{ $phieuXuat->Ma_don_hang_ban }}</span>
      <span style="font-size: 12px; color: #6B5559;">({{ $phieuXuat->donHangBan?->Thoi_diem_xac_nhan?->format('d/m/Y') }})</span>
    </div>
    <div>
      <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Khách hàng</span>
      <span style="font-weight: 600; color: #241417;">{{ $phieuXuat->donHangBan?->khachHang?->Ten_khach_hang ?? '—' }}</span>
      <span style="font-size: 12px; color: #6B5559;">({{ $phieuXuat->donHangBan?->Ma_khach_hang }})</span>
    </div>
    <div>
      <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Người nhận</span>
      <span style="font-weight: 600; color: #241417;">{{ $phieuXuat->donHangBan?->Ten_nguoi_nhan }}</span>
      <span style="font-size: 12px; color: #6B5559;">· {{ $phieuXuat->donHangBan?->Sdt_nguoi_nhan }}</span>
    </div>
    <div>
      <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Địa chỉ giao hàng</span>
      <span style="font-weight: 500; color: #241417;">{{ $phieuXuat->donHangBan?->Dia_chi_giao_hang }}</span>
    </div>
  </div>

  {{-- Bảng hàng cần lấy --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
    <h2 style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #5E1320;">Hàng cần lấy</h2>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 14px; min-width: 800px;">
        <thead>
          <tr style="background: #F8F0EE; text-align: left;">
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Lô phân bổ (FIFO)</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Lô thực xuất</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Số lượng</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Ghi chú</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @foreach($phieuXuat->chiTiets as $ct)
          @php
            $loPb = $ct->loPhanBo;
            $loTx = $ct->loThucXuat ?: $loPb;
            $sp   = $loPb?->sanPham ?? $loTx?->sanPham;
            $isSwapped = ($ct->Ma_lo_thuc_xuat && $ct->Ma_lo_thuc_xuat !== $ct->Ma_lo_phan_bo);

            // Kiểm tra tồn thực của lô thực xuất
            $tonThucLo = (int) DB::table('Ton_kho')
                ->where('Ma_kho', $phieuXuat->donHangBan->Ma_kho)
                ->where('Ma_lo', $loTx->Ma_lo ?? '')
                ->value('Ton_thuc');
            $lineThieu = ($phieuXuat->Trang_thai_giao_hang === 'Đang chuẩn bị hàng' && $tonThucLo < $ct->So_luong);
          @endphp
          <tr style="border-top: 1px solid #F0E6E3; {{ $lineThieu ? 'background: #FBEDED;' : ($isSwapped ? 'background: #FDF8F2;' : '') }}">
            <td style="padding: 12px 16px;">
              <div style="font-weight: 600; color: #241417;">{{ $sp?->Ten_san_pham ?? '—' }}</div>
              <div style="font-size: 12px; color: #6B5559;">{{ $sp?->Ma_san_pham }} · {{ $sp?->Dung_tich ?? '—' }} ml{{ $sp?->Nien_vu ? ' · ' . $sp->Nien_vu : '' }}</div>
            </td>
            <td style="padding: 12px 16px;">
              <strong>{{ $ct->Ma_lo_phan_bo }}</strong>
              @if($loPb?->Han_su_dung)
                <div style="font-size: 12px; color: #6B5559;">HSD: {{ date('d/m/Y', strtotime($loPb->Han_su_dung)) }}</div>
              @endif
            </td>
            <td style="padding: 12px 16px;">
              @if($isSwapped)
                <strong style="color: #8A4B08;">{{ $ct->Ma_lo_thuc_xuat }}</strong>
                @if($loTx?->Han_su_dung)
                  <div style="font-size: 12px; color: #6B5559;">HSD: {{ date('d/m/Y', strtotime($loTx->Han_su_dung)) }}</div>
                @endif
              @else
                <span style="color: #6B5559;">Như lô phân bổ</span>
              @endif
            </td>
            <td style="padding: 12px 16px; text-align: center; font-weight: 700;">
              {{ $ct->So_luong }} chai
            </td>
            <td style="padding: 12px 16px; font-size: 13px;">
              @if($lineThieu)
                <span style="color: #B42318; font-weight: 600;">⚠ Thiếu tồn (còn {{ $tonThucLo }}) – cần đổi lô</span>
              @elseif($isSwapped)
                <div style="color: #8A4B08;">
                  <strong>Đã đổi lô:</strong> {{ $ct->Ly_do_doi_lo }}
                  @if($ct->So_luong_hong)
                    <br><span style="color: #9B1C1C;">(Hỏng: {{ $ct->So_luong_hong }} chai)</span>
                  @endif
                </div>
              @else
                <span style="color: #9B7570;">—</span>
              @endif
            </td>
            <td style="padding: 12px 16px; text-align: center;">
              @if($phieuXuat->Trang_thai_giao_hang === 'Đang chuẩn bị hàng')
                <button type="button" onclick="openModalDoiLo('{{ $ct->Ma_lo_phan_bo }}', '{{ $ct->Ma_lo_thuc_xuat ?: $ct->Ma_lo_phan_bo }}', {{ $ct->So_luong }}, '{{ addslashes($sp?->Ten_san_pham ?? '') }}')"
                  style="height: 34px; padding: 0 14px; border-radius: 6px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
                  Đổi lô
                </button>
              @else
                <span style="color: #9B7570;">—</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Thông tin vận chuyển --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
    <h2 style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #5E1320;">Thông tin vận chuyển</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; font-size: 14px;">
      <div>
        <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Đơn vị vận chuyển</span>
        <strong>{{ $phieuXuat->Don_vi_van_chuyen ?: '—' }}</strong>
      </div>
      <div>
        <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Mã vận đơn</span>
        <strong>{{ $phieuXuat->Ma_van_don ?: '—' }}</strong>
      </div>
      <div>
        <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Người xuất</span>
        <span>{{ $phieuXuat->nguoiXuat?->Ho_ten ?: '—' }}</span>
      </div>
      <div>
        <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Thời điểm xuất kho</span>
        <span>{{ $phieuXuat->Thoi_diem_xuat_kho ? \Carbon\Carbon::parse($phieuXuat->Thoi_diem_xuat_kho)->format('d/m/Y H:i') : '—' }}</span>
      </div>
      @if($phieuXuat->Thoi_diem_ket_qua_giao)
      <div>
        <span style="font-size: 12px; color: #6B5559; display: block; margin-bottom: 4px;">Thời điểm kết quả giao</span>
        <span>{{ \Carbon\Carbon::parse($phieuXuat->Thoi_diem_ket_qua_giao)->format('d/m/Y H:i') }}</span>
      </div>
      @endif
      @if($phieuXuat->Ly_do_giao_that_bai)
      <div style="grid-column: span 2;">
        <span style="font-size: 12px; color: #9B1C1C; display: block; margin-bottom: 4px;">Lý do giao thất bại</span>
        <strong style="color: #9B1C1C;">{{ $phieuXuat->Ly_do_giao_that_bai }}</strong>
      </div>
      @endif
    </div>
  </div>

</div>

{{-- ============================================================== --}}
{{-- MODALS                                                         --}}
{{-- ============================================================== --}}

{{-- Modal 1: Xác nhận xuất kho --}}
<div id="modal-xuat-kho" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #fff; border-radius: 12px; padding: 24px; width: 480px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <h3 style="font-size: 18px; font-weight: 700; color: #5E1320; margin: 0 0 8px;">Xác nhận xuất kho</h3>
    <p style="font-size: 13px; color: #6B5559; margin: 0 0 16px;">Tồn thực sẽ bị trừ theo lô thực xuất, phiếu chuyển sang "Đang giao". Vui lòng nhập thông tin vận chuyển.</p>

    <form method="POST" action="{{ route('nv-kho.phieu-xuat.xuat-kho', $phieuXuat->Ma_phieu_xuat) }}">
      @csrf
      <div style="margin-bottom: 12px;">
        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Đơn vị vận chuyển *</label>
        <input type="text" name="don_vi_van_chuyen" value="{{ $phieuXuat->Don_vi_van_chuyen ?: old('don_vi_van_chuyen') }}" placeholder="VD: GHN, Viettel Post, Xe công ty..." required
          style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Mã vận đơn *</label>
        <input type="text" name="ma_van_don" value="{{ $phieuXuat->Ma_van_don ?: old('ma_van_don') }}" placeholder="Nhập mã vận đơn..." required
          style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-xuat-kho').style.display='none'"
          style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; cursor: pointer;">Hủy bỏ</button>
        <button type="submit"
          style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #fff; font-weight: 700; cursor: pointer;">Đồng ý xuất kho</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal 2: Sửa thông tin vận chuyển --}}
<div id="modal-van-chuyen" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #fff; border-radius: 12px; padding: 24px; width: 480px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <h3 style="font-size: 18px; font-weight: 700; color: #5E1320; margin: 0 0 16px;">Sửa thông tin vận chuyển</h3>

    <form method="POST" action="{{ route('nv-kho.phieu-xuat.update-van-chuyen', $phieuXuat->Ma_phieu_xuat) }}">
      @csrf
      @method('PUT')
      <div style="margin-bottom: 12px;">
        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Đơn vị vận chuyển *</label>
        <input type="text" name="don_vi_van_chuyen" value="{{ $phieuXuat->Don_vi_van_chuyen }}" required
          style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Mã vận đơn *</label>
        <input type="text" name="ma_van_don" value="{{ $phieuXuat->Ma_van_don }}" required
          style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-van-chuyen').style.display='none'"
          style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; cursor: pointer;">Hủy bỏ</button>
        <button type="submit"
          style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #fff; font-weight: 700; cursor: pointer;">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal 3: Giao thành công --}}
<div id="modal-giao-thanh-cong" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #fff; border-radius: 12px; padding: 24px; width: 440px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <h3 style="font-size: 18px; font-weight: 700; color: #03543F; margin: 0 0 8px;">Xác nhận Giao thành công?</h3>
    <p style="font-size: 14px; color: #6B5559; margin: 0 0 20px;">Xác nhận khách hàng đã nhận đủ hàng? Đơn hàng bán sẽ chuyển sang trạng thái "Hoàn thành".</p>

    <form method="POST" action="{{ route('nv-kho.phieu-xuat.cap-nhat-giao-hang', $phieuXuat->Ma_phieu_xuat) }}">
      @csrf
      <input type="hidden" name="ket_qua" value="Giao thành công">
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-giao-thanh-cong').style.display='none'"
          style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; cursor: pointer;">Quay lại</button>
        <button type="submit"
          style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #03543F; color: #fff; font-weight: 700; cursor: pointer;">Xác nhận hoàn thành</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal 4: Giao thất bại --}}
<div id="modal-giao-that-bai" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #fff; border-radius: 12px; padding: 24px; width: 480px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <h3 style="font-size: 18px; font-weight: 700; color: #9B1C1C; margin: 0 0 8px;">Ghi nhận Giao thất bại</h3>
    <p style="font-size: 13px; color: #6B5559; margin: 0 0 16px;">Hàng hoàn về kho sẽ được theo dõi và xử lý ở mục Nhập kho → Hoàn giao thất bại.</p>

    <form method="POST" action="{{ route('nv-kho.phieu-xuat.cap-nhat-giao-hang', $phieuXuat->Ma_phieu_xuat) }}">
      @csrf
      <input type="hidden" name="ket_qua" value="Giao hàng thất bại">
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Lý do giao thất bại *</label>
        <textarea name="ly_do_giao_that_bai" rows="3" required placeholder="VD: khách không nghe máy, sai địa chỉ, khách từ chối nhận..."
          style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; resize: vertical; box-sizing: border-box;"></textarea>
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-giao-that-bai').style.display='none'"
          style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; cursor: pointer;">Quay lại</button>
        <button type="submit"
          style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #9B1C1C; color: #fff; font-weight: 700; cursor: pointer;">Xác nhận giao thất bại</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal 5: ĐỔI LÔ --}}
<div id="modal-doi-lo" style="display: none; position: fixed; inset: 0; background: rgba(36, 20, 23, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
  <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; width: 100%; max-width: 760px; padding: 24px; box-shadow: 0 24px 60px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h2 id="modal-doi-lo-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #5E1320;">Đổi lô</h2>
      <button type="button" onclick="document.getElementById('modal-doi-lo').style.display='none'" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #EADFDC; background: #fff; font-size: 18px; cursor: pointer;">×</button>
    </div>

    <form method="POST" action="{{ route('nv-kho.phieu-xuat.doi-lo', $phieuXuat->Ma_phieu_xuat) }}" id="form-doi-lo">
      @csrf
      <input type="hidden" name="ma_lo_phan_bo" id="input-ma-lo-pb">
      <input type="hidden" name="ma_lo_thuc_xuat" id="input-ma-lo-tx">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 12px; color: #6B5559; margin-bottom: 4px;">Lô đang chọn</label>
          <input type="text" id="display-lo-goc" readonly style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; background: #F6F1EF; color: #6B5559; font: inherit; font-size: 13px; box-sizing: border-box;">
        </div>

        <div>
          <label style="display: block; font-size: 12px; font-weight: 600; color: #241417; margin-bottom: 4px;">Số lượng cần đổi *</label>
          <input type="number" name="so_luong_doi" id="input-sl-doi" min="1" required
            style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;" onchange="validateSwap()">
        </div>

        <div>
          <label style="display: block; font-size: 12px; font-weight: 600; color: #241417; margin-bottom: 4px;">Lý do *</label>
          <select name="ly_do_doi_lo" id="select-ly-do" style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; background: #fff;" onchange="onReasonChange(this.value)">
            <option value="Lấy lô khác cho tiện">Lấy lô khác cho tiện</option>
            <option value="Không tìm thấy lô gốc">Không tìm thấy lô gốc</option>
            <option value="Hỏng hoặc vỡ">Hỏng hoặc vỡ</option>
            <option value="Khác">Khác</option>
          </select>
        </div>

        <div id="box-sl-hong" style="display: none;">
          <label style="display: block; font-size: 12px; font-weight: 600; color: #9B1C1C; margin-bottom: 4px;">Số lượng hỏng *</label>
          <input type="number" name="so_luong_hong" id="input-sl-hong" min="1"
            style="width: 100%; height: 38px; padding: 0 10px; border-radius: 6px; border: 1px solid #F8B4B4; background: #FDF2F2; font: inherit; font-size: 14px; box-sizing: border-box;">
        </div>
      </div>

      <div style="margin-bottom: 16px;">
        <div style="font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 8px;">
          Chọn lô thay thế (cùng sản phẩm, còn khả dụng, xếp theo FIFO)
        </div>
        <div id="swap-table-container" style="border: 1px solid #EDE0DC; border-radius: 8px; overflow: hidden;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8F0EE;">
                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Mã lô</th>
                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Ngày nhập đầu</th>
                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Hạn sử dụng</th>
                <th style="padding: 10px 12px; text-align: right; font-weight: 600;">Tồn khả dụng</th>
                <th style="padding: 10px 12px; text-align: right; font-weight: 600; width: 100px;">Lấy</th>
              </tr>
            </thead>
            <tbody id="swap-table-body">
              <tr><td colspan="5" style="padding: 20px; text-align: center; color: #6B5559;">Đang tải danh sách lô thay thế...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      {{-- Cảnh báo không còn lô nào đủ --}}
      <div id="alert-no-lot" role="alert" style="display: none; padding: 12px 16px; border-radius: 8px; background: #FBEDED; border: 1px solid #E8C4C4; font-size: 13px; color: #3B2A2D; margin-bottom: 16px;">
        <b style="color: #B42318; display: block; margin-bottom: 4px;">Không còn lô nào đủ hàng</b>
        <span id="alert-no-lot-text">Tổng tồn khả dụng các lô khác của sản phẩm tại kho không đủ số lượng cần đổi. Đơn hàng này có thể cần báo Kinh doanh để hủy đơn.</span>
      </div>

      <div style="font-size: 12px; color: #6B5559; margin-bottom: 16px;">
        * Tổng số lấy phải bằng số lượng cần đổi. Hàng hỏng sẽ bị trừ khỏi tồn thực của lô gốc ngay lập tức.
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('modal-doi-lo').style.display='none'"
          style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; cursor: pointer;">Hủy bỏ</button>
        <button type="submit" id="btn-submit-doi-lo"
          style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #fff; font-weight: 700; cursor: pointer;">Xác nhận đổi lô</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModalXuatKho() {
  document.getElementById('modal-xuat-kho').style.display = 'flex';
}
function openModalEditVanChuyen() {
  document.getElementById('modal-van-chuyen').style.display = 'flex';
}
function openModalGiaoThanhCong() {
  document.getElementById('modal-giao-thanh-cong').style.display = 'flex';
}
function openModalGiaoThatBai() {
  document.getElementById('modal-giao-that-bai').style.display = 'flex';
}

function onReasonChange(val) {
  const boxHong = document.getElementById('box-sl-hong');
  const inputHong = document.getElementById('input-sl-hong');
  if (val === 'Hỏng hoặc vỡ') {
    boxHong.style.display = 'block';
    inputHong.required = true;
    inputHong.value = document.getElementById('input-sl-doi').value || 1;
  } else {
    boxHong.style.display = 'none';
    inputHong.required = false;
    inputHong.value = '';
  }
}

function openModalDoiLo(maLoPb, maLoTx, maxSl, tenSp) {
  document.getElementById('modal-doi-lo').style.display = 'flex';
  document.getElementById('modal-doi-lo-title').textContent = 'Đổi lô · ' + tenSp;
  document.getElementById('input-ma-lo-pb').value = maLoPb;
  document.getElementById('input-ma-lo-tx').value = maLoTx;
  document.getElementById('display-lo-goc').value = maLoTx + ' · ' + maxSl + ' chai';

  const inputSlDoi = document.getElementById('input-sl-doi');
  inputSlDoi.max = maxSl;
  inputSlDoi.value = maxSl;

  onReasonChange(document.getElementById('select-ly-do').value);

  // Load replacement lots via AJAX
  const tbody = document.getElementById('swap-table-body');
  tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #6B5559;">Đang tải danh sách lô thay thế...</td></tr>';

  fetch('{{ route('nv-kho.phieu-xuat.api-lo-thay-the', $phieuXuat->Ma_phieu_xuat) }}?ma_lo_goc=' + encodeURIComponent(maLoTx))
    .then(r => r.json())
    .then(data => {
      tbody.innerHTML = '';
      if (!data.lots || data.lots.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #9B1C1C;">Không có lô thay thế nào còn khả dụng.</td></tr>';
        showNoLotAlert(0, maxSl);
        return;
      }

      data.lots.forEach((l, idx) => {
        const tr = document.createElement('tr');
        tr.style.borderTop = '1px solid #F0E6E3';
        tr.innerHTML = `
          <td style="padding: 10px 12px; font-weight: 600;">
            <input type="hidden" name="replacements[${idx}][ma_lo]" value="${l.ma_lo}">
            ${l.ma_lo}
            ${l.ma_lo_ncc ? `<span style="font-size: 11px; color: #6B5559;">(${l.ma_lo_ncc})</span>` : ''}
          </td>
          <td style="padding: 10px 12px; color: #6B5559;">${l.ngay_nhap_dau_tien}</td>
          <td style="padding: 10px 12px; color: #6B5559;">${l.han_su_dung}</td>
          <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: #03543F;">${l.kha_dung}</td>
          <td style="padding: 10px 12px; text-align: right;">
            <input type="number" name="replacements[${idx}][so_luong]" min="0" max="${l.kha_dung}" value="0"
              style="width: 80px; height: 32px; padding: 0 6px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; text-align: right;"
              onchange="validateSwap()">
          </td>
        `;
        tbody.appendChild(tr);
      });

      if (data.tong_kha_dung < maxSl) {
        showNoLotAlert(data.tong_kha_dung, maxSl);
      } else {
        document.getElementById('alert-no-lot').style.display = 'none';
        document.getElementById('btn-submit-doi-lo').disabled = false;
        document.getElementById('btn-submit-doi-lo').style.opacity = '1';
      }
    });
}

function showNoLotAlert(tongKd, need) {
  document.getElementById('alert-no-lot').style.display = 'block';
  document.getElementById('alert-no-lot-text').textContent =
    `Tổng tồn khả dụng các lô khác của sản phẩm tại kho này là ${tongKd} chai, cần ${need} chai. Phiếu xuất này không thể đổi đủ lô.`;
  document.getElementById('btn-submit-doi-lo').disabled = true;
  document.getElementById('btn-submit-doi-lo').style.opacity = '0.5';
}

function validateSwap() {
  const need = parseInt(document.getElementById('input-sl-doi').value || 0);
  let totalPicked = 0;
  document.querySelectorAll('input[name*="[so_luong]"]').forEach(inp => {
    totalPicked += parseInt(inp.value || 0);
  });

  const btn = document.getElementById('btn-submit-doi-lo');
  if (totalPicked !== need || need <= 0) {
    btn.style.opacity = '0.7';
  } else {
    btn.style.opacity = '1';
  }
}
</script>
@endsection

