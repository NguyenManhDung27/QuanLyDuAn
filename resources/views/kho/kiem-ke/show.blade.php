@extends('layouts.app')

@section('title', 'Chi tiết phiếu kiểm kê – Soul Wine')

@section('content')
@php
  $badgeStyles = [
    'Đang kiểm' => 'background: #E3EAFB; color: #1D3F9E',
    'Chờ duyệt' => 'background: #FCEFD9; color: #8A4B08',
    'Hoàn tất'  => 'background: #E5F2EA; color: #1E5B36',
    'Đã hủy'    => 'background: #EEEAE9; color: #6B5559',
  ];
  $badge  = $badgeStyles[$pkk->Trang_thai] ?? 'background: #EEEAE9; color: #6B5559';
  $isWaiting  = $pkk->Trang_thai === 'Chờ duyệt';
  $isCounting = $pkk->Trang_thai === 'Đang kiểm';
  $isDone     = $pkk->Trang_thai === 'Hoàn tất';
  $isCancelled = $pkk->Trang_thai === 'Đã hủy';
  $tongLo = $pkk->chiTiets->count();
  $daDem  = $pkk->chiTiets->whereNotNull('So_luong_thuc_te')->count();
  $soDongLech = $pkk->chiTiets->filter(fn($c) => $c->So_luong_thuc_te !== null && (int)$c->So_luong_thuc_te !== (int)$c->So_luong_he_thong)->count();
@endphp

<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Breadcrumb --}}
  <div style="font-size: 13px; color: #6B5559;">
    <a href="{{ route('kho.kiem-ke.index') }}" style="color: #6B5559; text-decoration: none;">Phiếu kiểm kê</a>
    &nbsp;/&nbsp; <strong style="color: #241417;">{{ $pkk->Ma_phieu_kiem_ke }}</strong>
  </div>

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

  {{-- Header: tiêu đề + badge + actions --}}
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 14px;">
      <h1 style="font-size: 24px; font-weight: 700; color: #241417; margin: 0;">{{ $pkk->Ma_phieu_kiem_ke }}</h1>
      <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; white-space: nowrap; {{ $badge }}">
        {{ $pkk->Trang_thai }}
      </span>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      @if($isCounting)
        {{-- Hủy phiếu --}}
        <button type="button" onclick="document.getElementById('modal-huy').style.display='flex'"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 0; background: #F6E1E1; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Hủy phiếu
        </button>
      @elseif($isWaiting)
        {{-- Không duyệt --}}
        <button type="button" onclick="document.getElementById('modal-khongduyet').style.display='flex'"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Không duyệt
        </button>
        {{-- Duyệt --}}
        <button type="button" onclick="document.getElementById('modal-duyet').style.display='flex'"
                style="height: 44px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;"
                onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
          Duyệt và điều chỉnh tồn
        </button>
      @endif
    </div>
  </div>

  {{-- Banner trạng thái --}}
  @if($isCounting && $pkk->Ly_do_khong_duyet)
    <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 14px 18px; color: #B42318; font-size: 14px;">
      ⚠️ <strong>Đếm lại theo yêu cầu.</strong> Lý do: {{ $pkk->Ly_do_khong_duyet }}
    </div>
  @elseif($isCounting)
    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px 18px; color: #1D4ED8; font-size: 14px;">
      🔵 Nhân viên kho đang đếm. Đã đếm <strong>{{ $daDem }}/{{ $tongLo }}</strong> lô.
    </div>
  @elseif($isWaiting)
    <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 14px 18px; color: #92400E; font-size: 14px;">
      ⚠️ Có <strong>{{ $soDongLech }}</strong> lô chênh lệch. Duyệt để điều chỉnh tồn kho.
    </div>
  @endif

  <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start;">

    {{-- Thông tin phiếu --}}
    <section style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <h2 style="font-size: 15px; font-weight: 700; color: #241417; margin: 0 0 16px 0;">Thông tin phiếu</h2>
      <dl style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; margin: 0;">
        <div>
          <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Kho</dt>
          <dd style="margin: 0; font-weight: 500;">{{ $pkk->kho->Ten_kho ?? '—' }}</dd>
        </div>
        <div>
          <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Người tạo · Thời điểm tạo</dt>
          <dd style="margin: 0;">
            {{ $pkk->nguoiTao->Ho_ten ?? '—' }}
            @if($pkk->Thoi_diem_tao)
              <span style="color: #6B5559;">· {{ $pkk->Thoi_diem_tao->format('d/m/Y H:i') }}</span>
            @endif
          </dd>
        </div>
        <div>
          <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Người kiểm</dt>
          <dd style="margin: 0; {{ !$pkk->nguoiKiem ? 'color: #8A4B08; font-style: italic;' : '' }}">
            {{ $pkk->nguoiKiem->Ho_ten ?? 'Chưa giao' }}
          </dd>
        </div>
        <div>
          <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Đếm xong lúc</dt>
          <dd style="margin: 0; color: {{ $pkk->Thoi_diem_dem_xong ? '#241417' : '#6B5559' }};">
            {{ $pkk->Thoi_diem_dem_xong ? \Carbon\Carbon::parse($pkk->Thoi_diem_dem_xong)->format('d/m/Y H:i') : '—' }}
          </dd>
        </div>
        @if($isDone)
          <div>
            <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Người duyệt · Thời điểm duyệt</dt>
            <dd style="margin: 0;">
              {{ $pkk->nguoiDuyet->Ho_ten ?? '—' }}
              @if($pkk->Thoi_diem_duyet)
                <span style="color: #6B5559;">· {{ \Carbon\Carbon::parse($pkk->Thoi_diem_duyet)->format('d/m/Y H:i') }}</span>
              @endif
            </dd>
          </div>
        @endif
        @if($pkk->Ghi_chu)
          <div>
            <dt style="font-size: 12px; color: #6B5559; font-weight: 600; margin-bottom: 2px;">Ghi chú</dt>
            <dd style="margin: 0; color: #241417;">{{ $pkk->Ghi_chu }}</dd>
          </div>
        @endif
        @if($isCancelled && $pkk->Ly_do_huy)
          <div>
            <dt style="font-size: 12px; color: #B42318; font-weight: 600; margin-bottom: 2px;">Lý do hủy</dt>
            <dd style="margin: 0; color: #B42318;">{{ $pkk->Ly_do_huy }}</dd>
          </div>
        @endif
      </dl>
    </section>

    {{-- Kết quả đếm --}}
    <section style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 12px; padding: 20px;">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <h2 style="font-size: 15px; font-weight: 700; color: #241417; margin: 0;">Kết quả đếm</h2>
        <label style="display: flex; align-items: center; gap: 7px; font-size: 13px; color: #241417; cursor: pointer; user-select: none;">
          <input type="checkbox" id="chk-only-diff"
                 style="width: 16px; height: 16px; accent-color: #8B1E2D;">
          Chỉ hiện dòng chênh lệch
        </label>
      </div>

      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 800px; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #F6ECEA; text-align: left;">
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Số lô</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Hạn sử dụng</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Tồn hệ thống</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Thực tế</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320; text-align: right;">Chênh lệch</th>
              <th style="padding: 10px 14px; font-weight: 600; color: #5E1320;">Lý do chênh lệch</th>
            </tr>
          </thead>
          <tbody id="result-tbody">
            @foreach($pkk->chiTiets as $ct)
              @php
                $hasCounted = $ct->So_luong_thuc_te !== null;
                $diff = $hasCounted ? ((int)$ct->So_luong_thuc_te - (int)$ct->So_luong_he_thong) : null;
                $isDiff = $diff !== null && $diff !== 0;
                $rowBg  = $isDiff ? 'background: #FDF8F2;' : '';
                $diffTxt = $diff === null ? '—' : ($diff > 0 ? '+' . $diff : ($diff < 0 ? '−' . abs($diff) : '0'));
                $diffColor = $diff === null ? '#6B5559' : ($diff < 0 ? '#B42318' : ($diff > 0 ? '#1E5B36' : '#6B5559'));
                // F11 ưu tiên badge
                $isPrio = $ct->Is_uu_tien ?? false;
                $prioBadge = $ct->Ly_do_uu_tien ?? '';
              @endphp
              <tr class="result-row" data-is-diff="{{ $isDiff ? '1' : '0' }}"
                  style="border-top: 1px solid #F0E6E3; {{ $rowBg }}">
                <td style="padding: 10px 14px; font-weight: 500;">
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
                <td style="padding: 10px 14px; color: #6B5559;">{{ $ct->Ma_lo }}</td>
                <td style="padding: 10px 14px; color: #6B5559;">
                  {{ $ct->lo->Han_su_dung ? \Carbon\Carbon::parse($ct->lo->Han_su_dung)->format('d/m/Y') : '—' }}
                </td>
                <td style="padding: 10px 14px; text-align: right;">{{ number_format($ct->So_luong_he_thong) }}</td>
                <td style="padding: 10px 14px; text-align: right; color: {{ $hasCounted ? '#241417' : '#6B5559' }}; font-style: {{ $hasCounted ? 'normal' : 'italic' }};">
                  {{ $hasCounted ? number_format($ct->So_luong_thuc_te) : 'Chưa đếm' }}
                </td>
                <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: {{ $diffColor }};">
                  {{ $diffTxt }}
                </td>
                <td style="padding: 10px 14px; color: #241417;">
                  {{ $ct->Ly_do_chenh_lech ?: ($isDiff ? '—' : '') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  </div>

</div>

@if($isWaiting)
{{-- Modal: Duyệt --}}
<div id="modal-duyet"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; border-radius: 14px; padding: 28px; max-width: 440px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,.18);">
    <h3 style="font-size: 17px; font-weight: 700; color: #241417; margin: 0 0 10px 0;">Duyệt phiếu kiểm kê?</h3>
    <p style="font-size: 14px; color: #6B5559; margin: 0 0 20px 0;">
      Tồn kho các lô chênh lệch sẽ được điều chỉnh theo số thực tế. Thao tác <strong>không hoàn tác được</strong>.
    </p>
    <div style="display: flex; justify-content: flex-end; gap: 10px;">
      <button type="button" onclick="document.getElementById('modal-duyet').style.display='none'"
              style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; cursor: pointer;">
        Hủy bỏ
      </button>
      <form method="POST" action="{{ route('kho.kiem-ke.duyet', $pkk->Ma_phieu_kiem_ke) }}" style="margin: 0;">
        @csrf
        <button type="submit"
                style="height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Xác nhận duyệt
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Không duyệt --}}
<div id="modal-khongduyet"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; border-radius: 14px; padding: 28px; max-width: 480px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,.18);">
    <h3 style="font-size: 17px; font-weight: 700; color: #241417; margin: 0 0 10px 0;">Không duyệt phiếu?</h3>
    <p style="font-size: 14px; color: #6B5559; margin: 0 0 14px 0;">
      Phiếu trả về trạng thái <strong>Đang kiểm</strong> để nhân viên kho đếm lại các lô chênh lệch.
    </p>
    <form method="POST" action="{{ route('kho.kiem-ke.khong-duyet', $pkk->Ma_phieu_kiem_ke) }}">
      @csrf
      <label style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">
        Lý do <span style="color: #B42318;">*</span>
      </label>
      <textarea name="ly_do_khong_duyet" required rows="3"
                placeholder="Nhập lý do yêu cầu đếm lại…"
                style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; resize: vertical; box-sizing: border-box; margin-bottom: 16px;"></textarea>
      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="document.getElementById('modal-khongduyet').style.display='none'"
                style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; cursor: pointer;">
          Hủy bỏ
        </button>
        <button type="submit"
                style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Xác nhận không duyệt
        </button>
      </div>
    </form>
  </div>
</div>
@endif

@if($isCounting || $isWaiting)
{{-- Modal: Hủy phiếu --}}
<div id="modal-huy"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; border-radius: 14px; padding: 28px; max-width: 480px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,.18);">
    <h3 style="font-size: 17px; font-weight: 700; color: #241417; margin: 0 0 10px 0;">Hủy phiếu kiểm kê?</h3>
    <p style="font-size: 14px; color: #6B5559; margin: 0 0 14px 0;">
      Số đã đếm sẽ không được dùng. Lô sẽ được mở khóa.
    </p>
    <form method="POST" action="{{ route('kho.kiem-ke.huy', $pkk->Ma_phieu_kiem_ke) }}">
      @csrf
      <label style="display: block; font-size: 13px; font-weight: 600; color: #5E1320; margin-bottom: 6px;">
        Lý do <span style="color: #B42318;">*</span>
      </label>
      <textarea name="ly_do_huy" required rows="3"
                placeholder="Nhập lý do hủy phiếu…"
                style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; resize: vertical; box-sizing: border-box; margin-bottom: 16px;"></textarea>
      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="document.getElementById('modal-huy').style.display='none'"
                style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; cursor: pointer;">
          Đóng
        </button>
        <button type="submit"
                style="height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #F6E1E1; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Xác nhận hủy
        </button>
      </div>
    </form>
  </div>
</div>
@endif

<script>
// Chỉ hiện dòng chênh lệch
document.getElementById('chk-only-diff').addEventListener('change', function () {
  var showOnlyDiff = this.checked;
  document.querySelectorAll('#result-tbody .result-row').forEach(function (row) {
    if (showOnlyDiff) {
      row.style.display = row.getAttribute('data-is-diff') === '1' ? '' : 'none';
    } else {
      row.style.display = '';
    }
  });
});
// Đóng modal khi click ngoài
['modal-duyet', 'modal-khongduyet', 'modal-huy'].forEach(function (id) {
  document.getElementById(id).addEventListener('click', function (e) {
    if (e.target === this) this.style.display = 'none';
  });
});
</script>
@endsection

