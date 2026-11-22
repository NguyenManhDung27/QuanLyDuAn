@extends('layouts.app')

@section('title', 'Phiếu kiểm kê – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Kiểm kê</span> &nbsp;/&nbsp; <strong style="color: #241417;">Phiếu kiểm kê</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Phiếu kiểm kê</h1>
    </div>
    <a href="{{ route('kho.kiem-ke.create') }}"
       style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;"
       onmouseover="this.style.background='#6E1423'" onmouseout="this.style.background='#8B1E2D'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      Tạo phiếu kiểm kê
    </a>
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

  {{-- Tab bộ lọc trạng thái --}}
  <div style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        ['key' => 'all',       'label' => 'Tất cả'],
        ['key' => 'Đang kiểm', 'label' => 'Đang kiểm'],
        ['key' => 'Chờ duyệt', 'label' => 'Chờ duyệt'],
        ['key' => 'Hoàn tất',  'label' => 'Hoàn tất'],
        ['key' => 'Đã hủy',    'label' => 'Đã hủy'],
      ];
      $countMap = [
        'all'       => $counts['all'],
        'Đang kiểm' => $counts['Đang kiểm'],
        'Chờ duyệt' => $counts['Chờ duyệt'],
        'Hoàn tất'  => $counts['Hoàn tất'],
        'Đã hủy'    => $counts['Đã hủy'],
      ];
    @endphp
    @foreach($tabs as $t)
      <a href="{{ route('kho.kiem-ke.index', ['trang_thai' => $t['key']]) }}"
         style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 999px; font: inherit; font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer;
                {{ $tab === $t['key'] ? 'background: #6E1423; color: #FFFFFF; border: 0;' : 'background: #FFFFFF; color: #241417; border: 1px solid #DCCAC6;' }}">
        {{ $t['label'] }}
        <span style="opacity: 0.75; font-size: 12px;">({{ $countMap[$t['key']] }})</span>
      </a>
    @endforeach
  </div>

  {{-- Form tìm kiếm --}}
  <form method="GET" action="{{ route('kho.kiem-ke.index') }}" style="display: flex; align-items: center; gap: 10px;">
    <input type="hidden" name="trang_thai" value="{{ $tab }}">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã phiếu, người kiểm…"
           style="flex: 1; height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF; max-width: 360px;">
    <button type="submit" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; cursor: pointer;">Tìm</button>
  </form>

  {{-- Bảng danh sách --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto;">
    <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã phiếu</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Thời điểm tạo</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Người kiểm</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Số lô</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đã đếm</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Dòng lệch</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Trạng thái</th>
          <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($phieuKiemKes as $pkk)
          @php
            $tongLo    = $pkk->chiTiets->count();
            $daDem     = $pkk->chiTiets->whereNotNull('So_luong_thuc_te')->count();
            $soDongLech = ($pkk->Trang_thai !== 'Đang kiểm' || $pkk->Thoi_diem_dem_xong)
              ? $pkk->chiTiets->filter(fn($c) => $c->So_luong_thuc_te !== null && (int)$c->So_luong_thuc_te !== (int)$c->So_luong_he_thong)->count()
              : null;

            $badgeStyles = [
              'Đang kiểm' => 'background: #E3EAFB; color: #1D3F9E',
              'Chờ duyệt' => 'background: #FCEFD9; color: #8A4B08',
              'Hoàn tất'  => 'background: #E5F2EA; color: #1E5B36',
              'Đã hủy'    => 'background: #EEEAE9; color: #6B5559',
            ];
            $badgeStyle = $badgeStyles[$pkk->Trang_thai] ?? 'background: #EEEAE9; color: #6B5559';
          @endphp
          <tr style="border-top: 1px solid #F0E6E3;">
            <td style="padding: 12px 16px; font-weight: 600;">{{ $pkk->Ma_phieu_kiem_ke }}</td>
            <td style="padding: 12px 16px; color: #6B5559;">{{ $pkk->Thoi_diem_tao ? $pkk->Thoi_diem_tao->format('d/m/Y H:i') : '—' }}</td>
            <td style="padding: 12px 16px; {{ !$pkk->nguoiKiem ? 'color: #8A4B08; font-style: italic;' : '' }}">
              {{ $pkk->nguoiKiem?->Ho_ten ?? 'Chưa giao' }}
            </td>
            <td style="padding: 12px 16px; text-align: right;">{{ $tongLo }}</td>
            <td style="padding: 12px 16px; text-align: right;">
              @if($pkk->Trang_thai === 'Đang kiểm' && !$pkk->Thoi_diem_dem_xong)
                {{ $daDem }}/{{ $tongLo }}
              @else
                {{ $tongLo }}/{{ $tongLo }}
              @endif
            </td>
            <td style="padding: 12px 16px; text-align: right;">
              @if($soDongLech === null)
                <span style="color: #6B5559;">—</span>
              @elseif($soDongLech > 0)
                <span style="color: #B42318; font-weight: 600;">{{ $soDongLech }}</span>
              @else
                <span style="color: #1E5B36;">0</span>
              @endif
            </td>
            <td style="padding: 12px 16px;">
              <span style="display: inline-flex; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; {{ $badgeStyle }}">
                {{ $pkk->Trang_thai }}
              </span>
            </td>
            <td style="padding: 12px 16px;">
              <a href="{{ route('kho.kiem-ke.show', $pkk->Ma_phieu_kiem_ke) }}" style="font-weight: 600; color: #8B1E2D; text-decoration: none;">Xem</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding: 32px 16px; text-align: center; color: #6B5559;">Không có phiếu kiểm kê nào.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($phieuKiemKes->hasPages())
    <div>{{ $phieuKiemKes->links() }}</div>
  @endif

  <p style="font-size: 13px; color: #6B5559;">
    Phiếu không có chênh lệch tự chuyển Hoàn tất khi nhân viên kho đếm xong. Phiếu có chênh lệch chuyển Chờ duyệt.
  </p>
</div>
@endsection

