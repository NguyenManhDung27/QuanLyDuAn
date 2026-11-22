@extends('layouts.app')

@section('title', 'Nhập hàng · Đơn ' . $don->Ma_don_dat_hang . ' – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <a href="{{ route('nv-kho.nhap-kho.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>Chọn đơn đặt hàng</span>
      </a>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Nhập hàng · Đơn {{ $don->Ma_don_dat_hang }}</h1>
      <span style="font-size: 14px; color: #6B5559;">
        {{ $don->nhaCungCap->Ten_ncc ?? $don->Ma_ncc }} · Dự kiến nhận đủ: {{ $don->Ngay_du_kien_nhan_du ? date('d/m/Y', strtotime($don->Ngay_du_kien_nhan_du)) : '—' }} · Đã giao: {{ $soLanDaGiao }} lần
      </span>
    </div>
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

  {{-- Form nhập hàng --}}
  <form method="POST" action="{{ route('nv-kho.nhap-kho.ncc.store', $don->Ma_don_dat_hang) }}" style="display: flex; flex-direction: column; gap: 20px;">
    @csrf

    {{-- Section Thông tin chứng từ NCC giao --}}
    <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
      <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px;">
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 240px; color: #241417;">
          <span>Số phiếu giao hàng của NCC</span>
          <input type="text" name="so_phieu_giao_ncc" value="{{ old('so_phieu_giao_ncc') }}" placeholder="Theo chứng từ NCC giao kèm..."
                 style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <label style="display: flex; align-items: center; gap: 10px; flex: 1 1 260px; min-height: 42px; font-size: 14px; font-weight: 500; color: #241417; cursor: pointer;">
          <input type="checkbox" name="dot_giao_cuoi" value="1" {{ old('dot_giao_cuoi') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #8B1E2D;">
          <span>Đợt giao cuối (NCC báo không giao thêm)</span>
        </label>
      </div>
    </section>

    {{-- Bảng sản phẩm và nhập lô --}}
    <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
      <table id="importTable" style="width: 100%; min-width: 1100px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; min-width: 220px;">Sản phẩm</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right; width: 80px;">Còn lại</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 160px;">Mã lô</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Ngày sản xuất</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 140px;">Hạn sử dụng</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right; width: 110px;">SL nhận đạt</th>
            <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 220px;">Hao hụt & Lý do</th>
          </tr>
        </thead>
        <tbody>
          @php $rowIdx = 0; @endphp
          @foreach($don->chiTiets as $pIdx => $ct)
            @php
              $sp = $ct->sanPham;
              $maSp = $ct->Ma_san_pham;
              $conLai = $tienDoMap[$maSp]['con_lai'] ?? $ct->So_luong_dat;
            @endphp
            <tr id="row_{{ $rowIdx }}" style="border-top: 1px solid #F0E6E3; vertical-align: top;">
              <td style="padding: 12px 16px;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                  <span style="font-weight: 600; color: #241417;">{{ $sp->Ten_san_pham }}</span>
                  <span style="font-size: 12px; color: #6B5559;">{{ $sp->Ma_san_pham }} · {{ $sp->Nien_vu ?? '' }} · {{ $sp->Dung_tich_ml }}ml</span>
                  <button type="button" onclick="addExtraRow('{{ $maSp }}', '{{ addslashes($sp->Ten_san_pham) }}', this)"
                          style="align-self: flex-start; padding: 0; border: 0; background: none; color: #8B1E2D; font: inherit; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: underline; margin-top: 4px;">
                    + Thêm lô cho sản phẩm này
                  </button>
                </div>
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #6B5559;">
                {{ number_format($conLai) }}
              </td>
              <td style="padding: 12px 16px;">
                <input type="hidden" name="items[{{ $rowIdx }}][ma_san_pham]" value="{{ $maSp }}">
                <input type="text" name="items[{{ $rowIdx }}][ma_lo]" placeholder="Mã lô trên thùng"
                       style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 140px; background: #FFFFFF;">
              </td>
              <td style="padding: 12px 16px;">
                <input type="date" name="items[{{ $rowIdx }}][ngay_san_xuat]"
                       style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">
              </td>
              <td style="padding: 12px 16px;">
                <input type="date" name="items[{{ $rowIdx }}][han_su_dung]"
                       style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">
              </td>
              <td style="padding: 12px 16px; text-align: right;">
                <input type="number" name="items[{{ $rowIdx }}][so_luong_nhap]" min="0" placeholder="0" value="{{ $conLai }}"
                       style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 80px; text-align: right; background: #FFFFFF;">
              </td>
              <td style="padding: 12px 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                  <input type="number" name="items[{{ $rowIdx }}][so_luong_hao_hut]" min="0" placeholder="0" value="0"
                         oninput="toggleLossReason(this, {{ $rowIdx }})"
                         style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 80px; text-align: right; background: #FFFFFF;">
                  <input type="text" id="loss_reason_{{ $rowIdx }}" name="items[{{ $rowIdx }}][ly_do_hao_hut]" placeholder="Lý do hao hụt *"
                         style="display: none; height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #B45309; font: inherit; font-size: 13px; width: 190px; background: #FFFDF9;">
                </div>
              </td>
            </tr>
            @php $rowIdx++; @endphp
          @endforeach
        </tbody>
      </table>
    </div>

    <span style="font-size: 13px; color: #6B5559;">
      * Gõ mã lô in trên thùng; lô đã có thì ngày sản xuất, hạn sử dụng tự điền. Cùng một sản phẩm mà thùng hàng mang 2 mã lô khác nhau thì bấm "+ Thêm lô cho sản phẩm này" để nhập riêng từng lô. Không nhận sản phẩm nào trong đợt này thì để số lượng nhận là 0.
    </span>

    <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px;">
      <button type="submit" name="action" value="save"
              style="height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s ease;"
              onmouseover="this.style.background='#FDF2F2';" onmouseout="this.style.background='#FFFFFF';">
        Lưu nháp
      </button>

      <button type="submit" name="action" value="confirm"
              style="height: 44px; padding: 0 24px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.08);"
              onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
        Xác nhận nhập kho
      </button>
    </div>
  </form>
</div>

<script>
  var nextIdx = {{ $rowIdx }};

  function toggleLossReason(input, idx) {
    var reasonInput = document.getElementById('loss_reason_' + idx);
    if (!reasonInput) return;
    if (parseInt(input.value) > 0) {
      reasonInput.style.display = 'block';
      reasonInput.required = true;
    } else {
      reasonInput.style.display = 'none';
      reasonInput.required = false;
      reasonInput.value = '';
    }
  }

  function addExtraRow(maSp, tenSp, btn) {
    var parentTr = btn.closest('tr');
    var tr = document.createElement('tr');
    tr.id = 'row_' + nextIdx;
    tr.style.background = '#FCF8F7';
    tr.style.borderTop = '1px dashed #EADFDC';
    tr.style.verticalAlign = 'top';

    tr.innerHTML =
      '<td style="padding: 12px 16px;">' +
        '<div style="display: flex; flex-direction: column; gap: 4px; padding-left: 12px;">' +
          '<span style="font-size: 13px; color: #6B5559;">↳ Lô bổ sung: ' + tenSp + '</span>' +
          '<button type="button" onclick="this.closest(\'tr\').remove()" style="align-self: flex-start; padding: 0; border: 0; background: none; color: #9B1C1C; font: inherit; font-size: 12px; cursor: pointer; text-decoration: underline;">Bỏ dòng này</button>' +
        '</div>' +
      '</td>' +
      '<td style="padding: 12px 16px; text-align: right; color: #6B5559;">—</td>' +
      '<td style="padding: 12px 16px;">' +
        '<input type="hidden" name="items[' + nextIdx + '][ma_san_pham]" value="' + maSp + '">' +
        '<input type="text" name="items[' + nextIdx + '][ma_lo]" placeholder="Mã lô trên thùng" required style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 140px; background: #FFFFFF;">' +
      '</td>' +
      '<td style="padding: 12px 16px;">' +
        '<input type="date" name="items[' + nextIdx + '][ngay_san_xuat]" style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">' +
      '</td>' +
      '<td style="padding: 12px 16px;">' +
        '<input type="date" name="items[' + nextIdx + '][han_su_dung]" style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">' +
      '</td>' +
      '<td style="padding: 12px 16px; text-align: right;">' +
        '<input type="number" name="items[' + nextIdx + '][so_luong_nhap]" min="0" placeholder="0" value="0" style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 80px; text-align: right; background: #FFFFFF;">' +
      '</td>' +
      '<td style="padding: 12px 16px;">' +
        '<div style="display: flex; flex-direction: column; gap: 6px;">' +
          '<input type="number" name="items[' + nextIdx + '][so_luong_hao_hut]" min="0" placeholder="0" value="0" oninput="toggleLossReason(this, ' + nextIdx + ')" style="height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; width: 80px; text-align: right; background: #FFFFFF;">' +
          '<input type="text" id="loss_reason_' + nextIdx + '" name="items[' + nextIdx + '][ly_do_hao_hut]" placeholder="Lý do hao hụt *" style="display: none; height: 38px; padding: 0 8px; border-radius: 6px; border: 1px solid #B45309; font: inherit; font-size: 13px; width: 190px; background: #FFFDF9;">' +
        '</div>' +
      '</td>';

    parentTr.parentNode.insertBefore(tr, parentTr.nextSibling);
    nextIdx++;
  }
</script>
@endsection

