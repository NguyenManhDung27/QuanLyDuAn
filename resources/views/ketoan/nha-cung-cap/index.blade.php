@extends('layouts.app')

@section('title', 'Danh mục nhà cung cấp – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Nhà cung cấp</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Nhà cung cấp</h1>
      <p style="margin: 4px 0 0 0; font-size: 14px; color: #6B5559;">
        Xem thông tin liên hệ và trạng thái giao dịch của các nhà cung cấp rượu vang.
      </p>
    </div>
  </div>

  {{-- Thanh tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('ke-toan.nha-cung-cap.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 240px;">
      Tìm kiếm
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên nhà cung cấp, mã số thuế" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
      Quốc gia
      <select name="quoc_gia" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        @foreach($dsQuocGia as $qg)
          <option value="{{ $qg }}" {{ request('quoc_gia') == $qg ? 'selected' : '' }}>{{ $qg }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 160px;">
      Trạng thái
      <select name="trang_thai" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        <option value="Đang giao dịch" {{ request('trang_thai') == 'Đang giao dịch' ? 'selected' : '' }}>Đang giao dịch</option>
        <option value="Ngừng giao dịch" {{ request('trang_thai') == 'Ngừng giao dịch' ? 'selected' : '' }}>Ngừng giao dịch</option>
      </select>
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('ke-toan.nha-cung-cap.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bảng danh sách KT-06 --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600;">Mã NCC</th>
            <th style="padding: 12px 16px; font-weight: 600;">Tên nhà cung cấp</th>
            <th style="padding: 12px 16px; font-weight: 600;">Quốc gia</th>
            <th style="padding: 12px 16px; font-weight: 600;">Mã số thuế</th>
            <th style="padding: 12px 16px; font-weight: 600;">Người liên hệ</th>
            <th style="padding: 12px 16px; font-weight: 600;">Điện thoại</th>
            <th style="padding: 12px 16px; font-weight: 600;">Email</th>
            <th style="padding: 12px 16px; font-weight: 600;">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          @forelse($nhaCungCaps as $ncc)
            <tr style="border-top: 1px solid #F0E6E3;">
              <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $ncc->Ma_ncc }}</td>
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">{{ $ncc->Ten_ncc }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Quoc_gia }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Ma_so_thue ?? '—' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Nguoi_lien_he ?? '—' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->So_dien_thoai ?? '—' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Email ?? '—' }}</td>
              <td style="padding: 12px 16px;">
                @if($ncc->Trang_thai === 'Đang giao dịch')
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                    Đang giao dịch
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #F3F4F6; color: #6B7280;">
                    Ngừng giao dịch
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding: 30px; text-align: center; color: #6B5559;">Không có nhà cung cấp nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($nhaCungCaps->hasPages())
      <div style="padding: 14px 18px; border-top: 1px solid #EADFDC;">
        {{ $nhaCungCaps->links() }}
      </div>
    @endif
  </div>

  <div style="font-size: 13px; color: #6B5559;">
    Chỉ xem. Thêm mới và điều chỉnh thông tin do Quản lý kho thực hiện.
  </div>
</div>
@endsection

