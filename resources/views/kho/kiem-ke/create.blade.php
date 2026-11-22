@extends('layouts.app')

@section('title', 'Tạo phiếu kiểm kê – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  {{-- Breadcrumb + tiêu đề --}}
  <div>
    <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
      <a href="{{ route('kho.kiem-ke.index') }}" style="color: #6B5559; text-decoration: none;">Phiếu kiểm kê</a>
      &nbsp;/&nbsp; <strong style="color: #241417;">Tạo phiếu kiểm kê</strong>
    </div>
    <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Tạo phiếu kiểm kê</h1>
  </div>

  {{-- Lỗi validation --}}
  @if($errors->any())
    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 14px 18px;">
      <ul style="margin: 0; padding: 0 0 0 18px; color: #B42318; font-size: 14px; display: flex; flex-direction: column; gap: 4px;">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('kho.kiem-ke.store') }}" id="form-tao-kiem-ke">
    @csrf

    {{-- Thông tin chung --}}
    <section style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
      <h2 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0 0 20px 0;">Thông tin chung</h2>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">Mã phiếu</label>
          <input type="text" value="{{ $maPKK }}" readonly
                 style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #6B5559; background: #F6ECEA; box-sizing: border-box;">
        </div>

        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">Kho kiểm kê</label>
          <input type="text" value="{{ $kho->Ten_kho }}" readonly
                 style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #6B5559; background: #F6ECEA; box-sizing: border-box;">
        </div>

        <div>
          <label for="ma_nguoi_kiem" style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">Người kiểm <span style="font-weight: 400; color: #6B5559;">(để trống = chưa giao)</span></label>
          <select id="ma_nguoi_kiem" name="ma_nguoi_kiem"
                  style="width: 100%; height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; box-sizing: border-box;">
            <option value="">— Chưa giao —</option>
            @foreach($nvKhos as $nv)
              <option value="{{ $nv->Ma_nguoi_dung }}" {{ old('ma_nguoi_kiem') == $nv->Ma_nguoi_dung ? 'selected' : '' }}>
                {{ $nv->Ho_ten }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="ghi_chu" style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">Ghi chú</label>
          <textarea id="ghi_chu" name="ghi_chu" rows="2"
                    style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; resize: vertical; box-sizing: border-box;">{{ old('ghi_chu') }}</textarea>
        </div>
      </div>
    </section>

    {{-- Phạm vi kiểm --}}
    <section style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; flex-wrap: wrap;">
        <div>
          <h2 style="font-size: 16px; font-weight: 700; color: #241417; margin: 0 0 4px 0;">Phạm vi kiểm</h2>
          <p style="font-size: 13px; color: #6B5559; margin: 0;">
            Chọn các lô cần kiểm đếm. Lô ưu tiên được xếp đầu và chọn sẵn.
          </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          {{-- Bộ lọc --}}
          <input type="text" id="filter-sp" placeholder="Tìm sản phẩm…"
                 style="height: 38px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 13px; color: #241417; background: #FFFFFF; width: 180px;">
          <span id="da-chon-label" style="font-size: 13px; color: #6B5559;"></span>
        </div>
      </div>

      @if($lots->isEmpty())
        <div style="padding: 32px; text-align: center; color: #6B5559;">Không có lô tồn kho nào trong kho này.</div>
      @else
        <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #F0E6E3;">
          <table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px;">
            <thead>
              <tr style="background: #F6ECEA; text-align: left;">
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; width: 44px;">
                  <input type="checkbox" id="chk-all" title="Chọn/bỏ tất cả"
                         style="width: 18px; height: 18px; accent-color: #8B1E2D; cursor: pointer;">
                </th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Số lô NCC</th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Hạn sử dụng</th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Ngày nhập đầu tiên</th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Tồn hệ thống</th>
                <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Ưu tiên</th>
              </tr>
            </thead>
            <tbody id="lot-tbody">
              @foreach($lots as $lot)
                @php
                  $isLocked  = $lot->is_locked ?? false;
                  $lockPkk   = $lot->locked_by ?? null;
                  $isPrio    = $lot->is_prio ?? false;
                  $prioBadge = $lot->why_prio ?? '';
                  $defaultOn = $isPrio && !$isLocked;
                  $rowBg     = $isPrio ? 'background: #FCF8F7;' : '';
                @endphp
                <tr class="lot-row {{ $isPrio ? 'prio-row' : '' }}"
                    data-sp="{{ strtolower($lot->Ten_san_pham ?? '') }}"
                    style="border-top: 1px solid #F0E6E3; {{ $rowBg }}">
                  <td style="padding: 10px 14px;">
                    @if($isLocked)
                      <input type="checkbox" disabled title="Lô đang bị khóa bởi phiếu {{ $lockPkk }}"
                             style="width: 18px; height: 18px; accent-color: #8B1E2D; opacity: 0.4; cursor: not-allowed;">
                    @else
                      <input type="checkbox" name="ma_los[]" value="{{ $lot->Ma_lo }}"
                             class="lot-chk" {{ $defaultOn ? 'checked' : '' }}
                             style="width: 18px; height: 18px; accent-color: #8B1E2D; cursor: pointer;">
                    @endif
                  </td>
                  <td style="padding: 10px 14px; font-weight: 500;">
                    {{ $lot->Ten_san_pham }}
                    @if($lot->Nien_vu) <span style="color: #6B5559;">· {{ $lot->Nien_vu }}</span>@endif
                    @if($lot->Dung_tich) <span style="color: #6B5559;">· {{ $lot->Dung_tich }} ml</span>@endif
                  </td>
                  <td style="padding: 10px 14px; color: #6B5559;">{{ $lot->Ma_lo_ncc ?? '—' }}</td>
                  <td style="padding: 10px 14px; color: #6B5559;">
                    {{ $lot->Han_su_dung ? \Carbon\Carbon::parse($lot->Han_su_dung)->format('d/m/Y') : '—' }}
                  </td>
                  <td style="padding: 10px 14px; color: #6B5559;">
                    {{ $lot->Ngay_nhap_dau_tien ? \Carbon\Carbon::parse($lot->Ngay_nhap_dau_tien)->format('d/m/Y') : '—' }}
                  </td>
                  <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                    {{ number_format($lot->Ton_thuc ?? 0) }}
                  </td>
                  <td style="padding: 10px 14px;">
                    @if($isPrio)
                      <span title="{{ $prioBadge }}"
                            style="display: inline-flex; padding: 3px 10px; border-radius: 999px; border: 1px solid #B45309; color: #8A4B08; font-size: 12px; font-weight: 600; white-space: nowrap;">
                        Ưu tiên · {{ $prioBadge }}
                      </span>
                    @endif
                    @if($isLocked)
                      <span title="Đang bị khóa bởi {{ $lockPkk }}"
                            style="display: inline-flex; padding: 3px 10px; border-radius: 999px; background: #EEEAE9; color: #6B5559; font-size: 12px; font-weight: 600;">
                        Đang kiểm ({{ $lockPkk }})
                      </span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <p style="font-size: 13px; color: #6B5559; margin-top: 10px;">
          Hệ thống tự đánh dấu ưu tiên cho lô từng bị đổi khi xuất (không tìm thấy hoặc hỏng, vỡ) kể từ lần kiểm kê trước.
          Các lô này được xếp lên đầu và chọn sẵn; nhân viên kho đếm trước.
        </p>
      @endif
    </section>

    {{-- Actions --}}
    <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px;">
      <a href="{{ route('kho.kiem-ke.index') }}"
         style="display: inline-flex; align-items: center; height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; text-decoration: none;">
        Hủy bỏ
      </a>
      <button type="submit"
              style="height: 44px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;"
              onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
        Tạo phiếu và giao cho người kiểm
      </button>
    </div>
  </form>
</div>

<script>
(function () {
  // Chọn/bỏ tất cả
  var chkAll = document.getElementById('chk-all');
  var chks   = document.querySelectorAll('.lot-chk');
  var label  = document.getElementById('da-chon-label');

  function updateLabel() {
    var n = document.querySelectorAll('.lot-chk:checked').length;
    label.textContent = 'Đã chọn ' + n + ' lô';
  }
  updateLabel();

  chkAll.addEventListener('change', function () {
    document.querySelectorAll('.lot-row:not([style*="display: none"]) .lot-chk').forEach(function (c) {
      c.checked = chkAll.checked;
    });
    updateLabel();
  });
  chks.forEach(function (c) { c.addEventListener('change', updateLabel); });

  // Lọc theo tên sản phẩm
  document.getElementById('filter-sp').addEventListener('input', function () {
    var q = this.value.trim().toLowerCase();
    document.querySelectorAll('.lot-row').forEach(function (row) {
      var sp = row.getAttribute('data-sp') || '';
      row.style.display = (!q || sp.includes(q)) ? '' : 'none';
    });
  });
})();
</script>
@endsection

