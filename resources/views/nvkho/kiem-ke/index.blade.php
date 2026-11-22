@extends('layouts.app')

@section('title', 'Phiếu kiểm kê – Soul Wine')

@section('content')
@php
  $badgeStyles = [
    'Đang kiểm' => 'background: #E3EAFB; color: #1D3F9E',
    'Chờ duyệt' => 'background: #FCEFD9; color: #8A4B08',
    'Hoàn tất'  => 'background: #E5F2EA; color: #1E5B36',
    'Đã hủy'    => 'background: #EEEAE9; color: #6B5559',
  ];
  $tab = request('tab', 'can-dem');
@endphp

<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Tiêu đề --}}
  <div>
    <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0 0 4px 0;">Phiếu kiểm kê</h1>
    <p style="font-size: 13px; color: #6B5559; margin: 0;">Phiếu được giao cho bạn hoặc chưa giao.</p>
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

  {{-- Tabs --}}
  <div style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        ['key' => 'can_dem',    'label' => 'Cần đếm',    'count' => $counts['can_dem']],
        ['key' => 'cho_duyet',  'label' => 'Chờ duyệt',  'count' => $counts['cho_duyet']],
        ['key' => 'hoan_tat',   'label' => 'Hoàn tất',   'count' => $counts['hoan_tat']],
        ['key' => 'da_huy',     'label' => 'Đã hủy',     'count' => $counts['da_huy']],
      ];
    @endphp
    @foreach($tabs as $t)
      <a href="{{ route('nv-kho.kiem-ke.index', ['tab' => $t['key']]) }}"
         style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 999px; font: inherit; font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer;
                {{ $tab === $t['key'] ? 'background: #6E1423; color: #FFFFFF; border: 0;' : 'background: #FFFFFF; color: #241417; border: 1px solid #DCCAC6;' }}">
        {{ $t['label'] }}
        <span style="opacity: 0.75; font-size: 12px;">({{ $t['count'] }})</span>
      </a>
    @endforeach
  </div>

  {{-- Bảng danh sách --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto;">
    <table style="width: 100%; min-width: 800px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã phiếu</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Thời điểm tạo</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Người kiểm</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Số lô</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đã đếm</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($phieuKiemKes as $pkk)
          @php
            $tongLo = $pkk->chiTiets->count();
            $daDem  = $pkk->chiTiets->whereNotNull('So_luong_thuc_te')->count();
            $bs     = $badgeStyles[$pkk->Trang_thai] ?? 'background: #EEEAE9; color: #6B5559';
            $isMe   = $pkk->Ma_nguoi_kiem == auth()->id();
            $unassigned = !$pkk->Ma_nguoi_kiem;
            // Nhãn thao tác
            if ($pkk->Trang_thai === 'Đang kiểm') {
              $actLabel = $unassigned ? 'Nhận và đếm' : 'Đếm';
            } else {
              $actLabel = 'Xem';
            }
          @endphp
          <tr style="border-top: 1px solid #F0E6E3;">
            <td style="padding: 12px 16px; font-weight: 600;">{{ $pkk->Ma_phieu_kiem_ke }}</td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $pkk->Thoi_diem_tao ? $pkk->Thoi_diem_tao->format('d/m/Y H:i') : '—' }}
            </td>
            <td style="padding: 12px 16px; {{ $unassigned ? 'color: #8A4B08; font-style: italic;' : '' }}">
              @if($unassigned)
                Chưa giao
              @elseif($isMe)
                Bạn
              @else
                {{ $pkk->nguoiKiem->Ho_ten ?? '—' }}
              @endif
            </td>
            <td style="padding: 12px 16px; text-align: right;">{{ $tongLo }}</td>
            <td style="padding: 12px 16px; text-align: right;">
              @if($pkk->Trang_thai === 'Đang kiểm')
                {{ $daDem }}/{{ $tongLo }}
              @else
                {{ $tongLo }}/{{ $tongLo }}
              @endif
            </td>
            <td style="padding: 12px 16px;">
              <span style="display: inline-flex; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $bs }}">
                {{ $pkk->Trang_thai }}
              </span>
            </td>
            <td style="padding: 12px 16px;">
              <a href="{{ route('nv-kho.kiem-ke.dem', $pkk->Ma_phieu_kiem_ke) }}"
                 style="font-weight: 600; color: #8B1E2D; text-decoration: none;">
                {{ $actLabel }}
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="padding: 32px 16px; text-align: center; color: #6B5559;">
              Không có phiếu nào.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($phieuKiemKes->hasPages())
    <div>{{ $phieuKiemKes->appends(request()->query())->links() }}</div>
  @endif

</div>
@endsection
