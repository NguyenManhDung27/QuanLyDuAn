@extends('layouts.app')

@section('title', 'Danh mục khách hàng – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Khách hàng</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Khách hàng</h1>
      <p style="margin: 4px 0 0 0; font-size: 14px; color: #6B5559;">
        Xem danh sách khách hàng cá nhân và doanh nghiệp giao dịch với Soul Wine.
      </p>
    </div>
  </div>

  {{-- Thanh tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('ke-toan.khach-hang.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 240px;">
      Tìm kiếm
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên, SĐT, mã số thuế" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 150px;">
      Loại khách hàng
      <select name="loai_khach_hang" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        <option value="Cá nhân" {{ request('loai_khach_hang') == 'Cá nhân' ? 'selected' : '' }}>Cá nhân</option>
        <option value="Doanh nghiệp" {{ request('loai_khach_hang') == 'Doanh nghiệp' ? 'selected' : '' }}>Doanh nghiệp</option>
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
    <a href="{{ route('ke-toan.khach-hang.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bảng danh sách KT-07 --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600;">Mã KH</th>
            <th style="padding: 12px 16px; font-weight: 600;">Tên khách hàng</th>
            <th style="padding: 12px 16px; font-weight: 600;">Loại</th>
            <th style="padding: 12px 16px; font-weight: 600;">Mã số thuế</th>
            <th style="padding: 12px 16px; font-weight: 600;">Điện thoại</th>
            <th style="padding: 12px 16px; font-weight: 600;">Email</th>
            <th style="padding: 12px 16px; font-weight: 600;">Địa chỉ</th>
            <th style="padding: 12px 16px; font-weight: 600;">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          @forelse($khachHangs as $kh)
            <tr style="border-top: 1px solid #F0E6E3;">
              <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $kh->Ma_khach_hang }}</td>
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">{{ $kh->Ten_khach_hang }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">
                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; background: #F6ECEA; color: #5E1320;">
                  {{ $kh->Loai_khach_hang }}
                </span>
              </td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $kh->Ma_so_thue ?? '—' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $kh->So_dien_thoai }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $kh->Email ?? '—' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $kh->Dia_chi ?? '—' }}</td>
              <td style="padding: 12px 16px;">
                @if($kh->Trang_thai === 'Đang giao dịch')
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
              <td colspan="8" style="padding: 30px; text-align: center; color: #6B5559;">Không có khách hàng nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($khachHangs->hasPages())
      <div style="padding: 14px 18px; border-top: 1px solid #EADFDC;">
        {{ $khachHangs->links() }}
      </div>
    @endif
  </div>

  <div style="font-size: 13px; color: #6B5559;">
    Chỉ xem. Thêm mới và điều chỉnh thông tin do Nhân viên kinh doanh thực hiện.
  </div>
</div>
@endsection

