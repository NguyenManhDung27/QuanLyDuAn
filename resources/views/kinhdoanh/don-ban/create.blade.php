@extends('layouts.app')

@section('title', 'Lập đơn bán – Soul Wine')

@section('content')
<div style="padding: 24px 32px; max-width: 1100px; margin: 0 auto;">

  <div style="margin-bottom: 24px;">
    <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
      <a href="{{ route('kinh-doanh.don-ban.index') }}" style="color: #8B1E2D; text-decoration: none;">Đơn hàng bán</a>
      &nbsp;/&nbsp; <strong style="color: #241417;">Lập đơn bán mới</strong>
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Lập đơn bán mới</h1>
  </div>

  @if(session('error'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div style="padding: 12px 18px; border-radius: 8px; background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; font-size: 14px; margin-bottom: 16px;">
      @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('kinh-doanh.don-ban.store') }}" id="don-ban-form">
    @csrf

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">

      {{-- Kho xuất --}}
      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px;">
        <h2 style="font-size: 15px; font-weight: 700; color: #5E1320; margin: 0 0 16px;">Thông tin đơn hàng</h2>

        <div style="display: flex; flex-direction: column; gap: 12px;">
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 4px;">Kho xuất *</label>
            <select name="ma_kho" id="ma_kho" style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; background: #fff;" onchange="loadTonKhaDung(this.value)">
              @foreach($khos as $kho)
                <option value="{{ $kho->Ma_kho }}" @selected(($kho->Ma_kho === $maKho) || old('ma_kho') === $kho->Ma_kho)>{{ $kho->Ten_kho }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 4px;">Khách hàng *</label>
            <select name="ma_khach_hang" id="ma_khach_hang" style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; background: #fff;" onchange="fillNguoiNhan(this)">
              <option value="">— Chọn khách hàng —</option>
              @foreach($khachHangs as $kh)
                <option value="{{ $kh->Ma_khach_hang }}"
                  data-ten="{{ $kh->Ten_khach_hang }}"
                  data-sdt="{{ $kh->So_dien_thoai ?? '' }}"
                  data-dc="{{ $kh->Dia_chi ?? '' }}"
                  @selected(old('ma_khach_hang') === $kh->Ma_khach_hang)>
                  {{ $kh->Ten_khach_hang }} – {{ $kh->Ma_khach_hang }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Thông tin giao hàng --}}
      <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px;">
        <h2 style="font-size: 15px; font-weight: 700; color: #5E1320; margin: 0 0 16px;">Thông tin giao hàng</h2>

        <div style="display: flex; flex-direction: column; gap: 12px;">
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 4px;">Tên người nhận *</label>
            <input type="text" name="ten_nguoi_nhan" id="ten_nguoi_nhan" value="{{ old('ten_nguoi_nhan') }}" placeholder="Họ và tên người nhận"
              style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
          </div>
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 4px;">SĐT người nhận *</label>
            <input type="text" name="sdt_nguoi_nhan" id="sdt_nguoi_nhan" value="{{ old('sdt_nguoi_nhan') }}" placeholder="Số điện thoại"
              style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
          </div>
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 4px;">Địa chỉ giao hàng *</label>
            <input type="text" name="dia_chi_giao_hang" id="dia_chi_giao_hang" value="{{ old('dia_chi_giao_hang') }}" placeholder="Địa chỉ giao"
              style="width: 100%; height: 40px; padding: 0 10px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 14px; box-sizing: border-box;">
          </div>
        </div>
      </div>
    </div>

    {{-- Danh sách sản phẩm --}}
    <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
      <h2 style="font-size: 15px; font-weight: 700; color: #5E1320; margin: 0 0 16px;">Danh sách sản phẩm</h2>

      <table style="width: 100%; border-collapse: collapse; font-size: 14px;" id="items-table">
        <thead>
          <tr style="background: #F8F0EE;">
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: left;">Sản phẩm</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: center; width: 100px;">Tồn KD</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 130px;">Giá niêm yết</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: center; width: 90px;">CK (%)</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 130px;">Đơn giá bán</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: center; width: 90px;">SL</th>
            <th style="padding: 10px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 150px;">Thành tiền</th>
          </tr>
        </thead>
        <tbody id="items-body">
          @foreach($sanPhams as $i => $sp)
          @php
            $ton = $tonKhaDung[$sp->Ma_san_pham] ?? 0;
            $coGia = !is_null($sp->Gia_niem_yet);
          @endphp
          <tr style="border-top: 1px solid #F3EAE7; {{ !$coGia ? 'opacity: 0.4;' : '' }}" data-ma="{{ $sp->Ma_san_pham }}" data-gia="{{ $sp->Gia_niem_yet ?? 0 }}" data-ton="{{ $ton }}">
            <td style="padding: 10px 12px;">
              <input type="hidden" name="items[{{ $i }}][ma_san_pham]" value="{{ $sp->Ma_san_pham }}">
              <div style="font-weight: 600;">{{ $sp->Ten_san_pham }}</div>
              <div style="font-size: 12px; color: #6B5559;">{{ $sp->Ma_san_pham }} · {{ $sp->Dung_tich ?? '—' }} ml{{ $sp->Nien_vu ? ' · ' . $sp->Nien_vu : '' }}</div>
              @if(!$coGia)
                <div style="font-size: 11px; color: #9B1C1C; margin-top: 2px;">Chưa có giá niêm yết</div>
              @endif
            </td>
            <td style="padding: 10px 12px; text-align: center;">
              <span class="ton-kha-dung" style="font-weight: 600; color: {{ $ton > 0 ? '#03543F' : '#9B1C1C' }};">{{ $ton }}</span>
            </td>
            <td style="padding: 10px 12px; text-align: right; color: #6B5559;">
              {{ $sp->Gia_niem_yet ? number_format($sp->Gia_niem_yet) . ' đ' : '—' }}
            </td>
            <td style="padding: 10px 12px; text-align: center;">
              @if($coGia)
              <input type="number" name="items[{{ $i }}][chiet_khau]" value="{{ old("items.{$i}.chiet_khau", 0) }}"
                min="0" max="100" step="0.01"
                style="width: 70px; height: 32px; padding: 0 6px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; text-align: center;"
                onchange="recalcRow(this.closest('tr'))" {{ !$coGia ? 'disabled' : '' }}>
              @else
                —
              @endif
            </td>
            <td style="padding: 10px 12px; text-align: right;">
              <span class="don-gia-ban" style="font-weight: 600; color: #241417;">
                {{ $coGia ? number_format($sp->Gia_niem_yet) . ' đ' : '—' }}
              </span>
            </td>
            <td style="padding: 10px 12px; text-align: center;">
              @if($coGia)
              <input type="number" name="items[{{ $i }}][so_luong]" value="{{ old("items.{$i}.so_luong", 0) }}"
                min="0" max="{{ $ton }}"
                style="width: 70px; height: 32px; padding: 0 6px; border-radius: 6px; border: 1px solid #D6C8C5; font: inherit; font-size: 13px; text-align: center;"
                onchange="recalcRow(this.closest('tr'))">
              @else
                <input type="hidden" name="items[{{ $i }}][so_luong]" value="0">
                —
              @endif
            </td>
            <td style="padding: 10px 12px; text-align: right;">
              <span class="thanh-tien" style="font-weight: 600; color: #8B1E2D;">0 đ</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Tổng kết --}}
    <div style="background: #fff; border: 1px solid #EDE0DC; border-radius: 10px; padding: 20px; display: flex; justify-content: flex-end;">
      <div style="min-width: 280px;">
        <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #6B5559;">
          <span>Tổng tiền hàng</span>
          <span id="tong-tien" style="font-weight: 600; color: #241417;">0 đ</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; color: #9B7570;">
          <span>Thuế GTGT 8% (tham khảo)</span>
          <span id="tong-thue">0 đ</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 10px 0; font-size: 16px; font-weight: 700; color: #241417; border-top: 1px solid #EDE0DC; margin-top: 6px;">
          <span>Tổng thanh toán (tham khảo)</span>
          <span id="tong-thanh-toan">0 đ</span>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px;">
          <a href="{{ route('kinh-doanh.don-ban.index') }}" style="height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #D6C8C5; background: #fff; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">Hủy</a>
          <button type="submit" style="height: 40px; padding: 0 24px; border-radius: 8px; border: 0; background: #8B1E2D; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
            Xác nhận đơn hàng
          </button>
        </div>
      </div>
    </div>

  </form>
</div>

<script>
const fmtVND = (n) => n.toLocaleString('vi-VN') + ' đ';

function recalcRow(tr) {
  const gia = parseInt(tr.dataset.gia || 0);
  const ckInput = tr.querySelector('input[name*="chiet_khau"]');
  const slInput = tr.querySelector('input[name*="so_luong"]');
  const donGiaEl = tr.querySelector('.don-gia-ban');
  const thanhTienEl = tr.querySelector('.thanh-tien');

  if (!gia || !slInput) return;

  const ck = parseFloat(ckInput?.value || 0);
  const sl = parseInt(slInput.value || 0);
  const donGia = Math.round(gia * (1 - ck / 100));

  if (donGiaEl) donGiaEl.textContent = fmtVND(donGia);
  if (thanhTienEl) thanhTienEl.textContent = fmtVND(sl * donGia);

  // Cảnh báo vượt tồn
  const ton = parseInt(tr.dataset.ton || 0);
  slInput.style.borderColor = sl > ton ? '#E53E3E' : '#D6C8C5';

  recalcTotal();
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('#items-body tr').forEach(tr => {
    const gia = parseInt(tr.dataset.gia || 0);
    const ckInput = tr.querySelector('input[name*="chiet_khau"]');
    const slInput = tr.querySelector('input[name*="so_luong"]');
    if (!gia || !slInput) return;
    const ck = parseFloat(ckInput?.value || 0);
    const sl = parseInt(slInput.value || 0);
    const donGia = Math.round(gia * (1 - ck / 100));
    total += sl * donGia;
  });
  document.getElementById('tong-tien').textContent = fmtVND(total);
  document.getElementById('tong-thue').textContent = fmtVND(Math.round(total * 0.08));
  document.getElementById('tong-thanh-toan').textContent = fmtVND(Math.round(total * 1.08));
}

function fillNguoiNhan(sel) {
  const opt = sel.selectedOptions[0];
  document.getElementById('ten_nguoi_nhan').value = opt.dataset.ten || '';
  document.getElementById('sdt_nguoi_nhan').value = opt.dataset.sdt || '';
  document.getElementById('dia_chi_giao_hang').value = opt.dataset.dc || '';
}

function loadTonKhaDung(maKho) {
  fetch('{{ route('kinh-doanh.don-ban.ton-kha-dung') }}?ma_kho=' + maKho)
    .then(r => r.json())
    .then(data => {
      document.querySelectorAll('#items-body tr').forEach(tr => {
        const ma = tr.dataset.ma;
        if (!ma) return;
        const ton = data[ma] ?? 0;
        tr.dataset.ton = ton;
        const tonEl = tr.querySelector('.ton-kha-dung');
        if (tonEl) {
          tonEl.textContent = ton;
          tonEl.style.color = ton > 0 ? '#03543F' : '#9B1C1C';
        }
        const slInput = tr.querySelector('input[name*="so_luong"]');
        if (slInput) slInput.max = ton;
      });
    });
}

// Initial calc
document.querySelectorAll('#items-body tr').forEach(tr => recalcRow(tr));
</script>
@endsection

