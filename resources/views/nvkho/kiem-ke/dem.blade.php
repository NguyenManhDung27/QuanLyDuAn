@extends('layouts.app')

@section('title', 'Đếm kiểm kê – Soul Wine')

@section('content')
@php
  $isEditable = $pkk->Trang_thai === 'Đang kiểm';
  $badgeStyles = [
    'Đang kiểm' => 'background: #E3EAFB; color: #1D3F9E',
    'Chờ duyệt' => 'background: #FCEFD9; color: #8A4B08',
    'Hoàn tất'  => 'background: #E5F2EA; color: #1E5B36',
    'Đã hủy'    => 'background: #EEEAE9; color: #6B5559',
  ];
  $badge  = $badgeStyles[$pkk->Trang_thai] ?? 'background: #EEEAE9; color: #6B5559';
  $tongLo = $pkk->chiTiets->count();
  $daDem  = $pkk->chiTiets->whereNotNull('So_luong_thuc_te')->count();
  $isMe   = $pkk->Ma_nguoi_kiem == auth()->id();
  $unassigned = !$pkk->Ma_nguoi_kiem;
@endphp

<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
    <div>
      <a href="{{ route('nv-kho.kiem-ke.index') }}"
         style="display: inline-flex; align-items: center; gap: 5px; font-size: 13px; color: #6B5559; text-decoration: none; margin-bottom: 6px;">
        ← Phiếu kiểm kê
      </a>
      <div style="display: flex; align-items: center; gap: 12px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #241417; margin: 0;">
          Đếm kiểm kê · {{ $pkk->Ma_phieu_kiem_ke }}
        </h1>
        <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $badge }}">
          {{ $pkk->Trang_thai }}
        </span>
      </div>
    </div>
    @if($isEditable)
      <div style="display: flex; gap: 10px;">
        <button type="button" id="btn-luu-tam"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; cursor: pointer;">
          Lưu tạm
        </button>
        <button type="button" id="btn-hoan-thanh"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;"
                onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
          Hoàn thành đếm
        </button>
      </div>
    @endif
  </div>

  {{-- Banner lý do không duyệt --}}
  @if($pkk->Ly_do_khong_duyet)
    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 14px 18px; color: #B42318; font-size: 14px;">
      ⚠️ <strong>Đếm lại theo yêu cầu.</strong> Lý do: {{ $pkk->Ly_do_khong_duyet }}
    </div>
  @endif

  {{-- Thông tin phiếu --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
    <div>
      <div style="font-size: 12px; font-weight: 600; color: #6B5559; margin-bottom: 2px;">Người tạo</div>
      <div style="font-size: 14px; color: #241417;">{{ $pkk->nguoiTao->Ho_ten ?? '—' }}</div>
    </div>
    <div>
      <div style="font-size: 12px; font-weight: 600; color: #6B5559; margin-bottom: 2px;">Người kiểm</div>
      <div style="font-size: 14px; color: #241417;">
        @if($unassigned)
          <span style="color: #8A4B08; font-style: italic;">Chưa giao</span>
        @elseif($isMe)
          Bạn
        @else
          {{ $pkk->nguoiKiem->Ho_ten ?? '—' }}
        @endif
      </div>
    </div>
    <div>
      <div style="font-size: 12px; font-weight: 600; color: #6B5559; margin-bottom: 2px;">Đã nhập</div>
      <div style="font-size: 14px; font-weight: 700; color: #241417;">{{ $daDem }}/{{ $tongLo }} lô</div>
    </div>
    @if($pkk->Ghi_chu)
      <div>
        <div style="font-size: 12px; font-weight: 600; color: #6B5559; margin-bottom: 2px;">Ghi chú</div>
        <div style="font-size: 14px; color: #241417;">{{ $pkk->Ghi_chu }}</div>
      </div>
    @endif
  </div>

  {{-- Form nhập số đếm --}}
  @if($isEditable)
    <form id="form-dem" method="POST" action="{{ route('nv-kho.kiem-ke.luu-dem', $pkk->Ma_phieu_kiem_ke) }}">
      @csrf
      <input type="hidden" name="action" id="form-action" value="luu_tam">
  @endif

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

  {{-- Bảng đếm --}}
  <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; overflow-x: auto;">
    <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã lô</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Hạn sử dụng</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Tồn hệ thống</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Thực tế</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Chênh lệch</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Lý do chênh lệch</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pkk->chiTiets as $ct)
          @php
            $isPrio    = $ct->Is_uu_tien ?? false;
            $prioBadge = $ct->Ly_do_uu_tien ?? '';
            $existVal  = $ct->So_luong_thuc_te;
            $existReason = $ct->Ly_do_chenh_lech ?? '';
          @endphp
          <tr class="dem-row" data-ma-lo="{{ $ct->Ma_lo }}" data-sys="{{ (int)$ct->So_luong_he_thong }}"
              style="border-top: 1px solid #F0E6E3;">
            <td style="padding: 12px 16px; font-weight: 500;">
              {{ $ct->lo->sanPham->Ten_san_pham ?? '—' }}
              @if($ct->lo->sanPham->Nien_vu ?? null)
                <span style="color: #6B5559;">· {{ $ct->lo->sanPham->Nien_vu }}</span>
              @endif
              @if($isPrio)
                <span style="display: inline-flex; padding: 2px 8px; border-radius: 999px; border: 1px solid #B45309; color: #8A4B08; font-size: 11px; font-weight: 600; margin-left: 4px;">
                  Ưu tiên · {{ $prioBadge }}
                </span>
              @endif
            </td>
            <td style="padding: 12px 16px; color: #6B5559;">{{ $ct->Ma_lo }}</td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $ct->lo->Han_su_dung ? \Carbon\Carbon::parse($ct->lo->Han_su_dung)->format('d/m/Y') : '—' }}
            </td>
            <td style="padding: 12px 16px; text-align: right;">{{ number_format($ct->So_luong_he_thong) }}</td>
            <td style="padding: 12px 16px; text-align: right;">
              @if($isEditable)
                <input type="number" min="0"
                       name="items[{{ $ct->Ma_lo }}][So_luong_thuc_te]"
                       value="{{ old('items.'.$ct->Ma_lo.'.So_luong_thuc_te', $existVal) }}"
                       placeholder="—"
                       class="inp-thucte"
                       data-sys="{{ (int)$ct->So_luong_he_thong }}"
                       data-row="{{ $ct->Ma_lo }}"
                       style="height: 40px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; width: 90px; text-align: right;">
              @else
                <span style="color: {{ $existVal !== null ? '#241417' : '#6B5559' }}; font-style: {{ $existVal !== null ? 'normal' : 'italic' }};">
                  {{ $existVal !== null ? number_format($existVal) : 'Chưa đếm' }}
                </span>
              @endif
            </td>
            <td style="padding: 12px 16px; text-align: right;">
              @if($isEditable)
                <span class="diff-label" data-row="{{ $ct->Ma_lo }}"
                      style="font-weight: 700; color: #6B5559;">—</span>
              @else
                @php
                  $d = $existVal !== null ? ((int)$existVal - (int)$ct->So_luong_he_thong) : null;
                  $dc = $d === null ? '#6B5559' : ($d < 0 ? '#B42318' : ($d > 0 ? '#1E5B36' : '#6B5559'));
                  $dt = $d === null ? '—' : ($d > 0 ? '+'.$d : ($d < 0 ? '−'.abs($d) : '0'));
                @endphp
                <span style="font-weight: 700; color: {{ $dc }};">{{ $dt }}</span>
              @endif
            </td>
            <td style="padding: 12px 16px;">
              @if($isEditable)
                <input type="text"
                       name="items[{{ $ct->Ma_lo }}][Ly_do_chenh_lech]"
                       value="{{ old('items.'.$ct->Ma_lo.'.Ly_do_chenh_lech', $existReason) }}"
                       placeholder="Bắt buộc nếu lệch"
                       class="inp-lydo"
                       data-row="{{ $ct->Ma_lo }}"
                       style="height: 40px; padding: 0 8px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; width: 100%; min-width: 180px; display: none;">
              @else
                <span style="color: #241417;">{{ $existReason }}</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <p style="font-size: 13px; color: #6B5559;">
    Lô ưu tiên xếp đầu: lô từng bị đổi khi xuất (không tìm thấy hoặc hỏng, vỡ).
    Hoàn thành đếm: khớp hết thì phiếu <strong>Hoàn tất</strong>; có chênh lệch thì phiếu chuyển <strong>Chờ duyệt</strong> cho Quản lý kho.
  </p>

  @if($isEditable)
    </form>
  @endif

</div>

@if($isEditable)
<script>
(function () {
  // Tính chênh lệch realtime
  document.querySelectorAll('.inp-thucte').forEach(function (inp) {
    inp.addEventListener('input', function () {
      var row  = this.getAttribute('data-row');
      var sys  = parseInt(this.getAttribute('data-sys'), 10);
      var val  = this.value.trim();
      var diffEl  = document.querySelector('.diff-label[data-row="' + row + '"]');
      var lyDoEl  = document.querySelector('.inp-lydo[data-row="' + row + '"]');

      if (val === '') {
        diffEl.textContent = '—';
        diffEl.style.color = '#6B5559';
        lyDoEl.style.display = 'none';
        lyDoEl.style.borderColor = '#DCCAC6';
        return;
      }
      var actual = parseInt(val, 10);
      var diff   = actual - sys;
      if (diff > 0) {
        diffEl.textContent = '+' + diff;
        diffEl.style.color = '#1E5B36';
      } else if (diff < 0) {
        diffEl.textContent = '−' + Math.abs(diff);
        diffEl.style.color = '#B42318';
      } else {
        diffEl.textContent = '0';
        diffEl.style.color = '#6B5559';
      }

      if (diff !== 0) {
        lyDoEl.style.display = '';
        lyDoEl.style.borderColor = '#B45309';
      } else {
        lyDoEl.style.display = 'none';
        lyDoEl.style.borderColor = '#DCCAC6';
      }
    });

    // Init nếu đã có giá trị (lưu tạm trước)
    if (inp.value !== '') inp.dispatchEvent(new Event('input'));
  });

  // Buttons
  document.getElementById('btn-luu-tam').addEventListener('click', function () {
    document.getElementById('form-action').value = 'luu_tam';
    document.getElementById('form-dem').submit();
  });
  document.getElementById('btn-hoan-thanh').addEventListener('click', function () {
    document.getElementById('form-action').value = 'hoan_thanh';
    document.getElementById('form-dem').submit();
  });
})();
</script>
@endif
@endsection

