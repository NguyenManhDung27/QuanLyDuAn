@extends('layouts.app')

@section('title', 'Danh mục sản phẩm – Kế toán – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Sản phẩm</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Sản phẩm</h1>
      <p style="margin: 4px 0 0 0; font-size: 14px; color: #6B5559;">
        Xem số lượng tồn theo kho, giá vốn bình quân và giá trị tồn kho toàn hệ thống.
      </p>
    </div>
  </div>

  {{-- Thanh tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('ke-toan.san-pham.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 240px;">
      Tìm kiếm
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên sản phẩm, nhà sản xuất" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 140px;">
      Loại vang
      <select name="loai_vang" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        <option value="Đỏ" {{ request('loai_vang') == 'Đỏ' ? 'selected' : '' }}>Đỏ</option>
        <option value="Trắng" {{ request('loai_vang') == 'Trắng' ? 'selected' : '' }}>Trắng</option>
        <option value="Hồng" {{ request('loai_vang') == 'Hồng' ? 'selected' : '' }}>Hồng</option>
        <option value="Vang nổ" {{ request('loai_vang') == 'Vang nổ' ? 'selected' : '' }}>Vang nổ</option>
        <option value="Khác" {{ request('loai_vang') == 'Khác' ? 'selected' : '' }}>Khác</option>
      </select>
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('ke-toan.san-pham.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bảng hiển thị KT-05 --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 1000px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
            <th style="padding: 12px 16px; font-weight: 600;">Mã SP</th>
            <th style="padding: 12px 16px; font-weight: 600;">Sản phẩm</th>
            <th style="padding: 12px 16px; font-weight: 600;">Loại</th>
            <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Tồn HN</th>
            <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Tồn HCM</th>
            <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Giá niêm yết</th>
            <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Giá vốn BQ</th>
            <th style="padding: 12px 16px; font-weight: 600; text-align: right;">Giá trị tồn</th>
            <th style="padding: 12px 16px; font-weight: 600;">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sanPhams as $sp)
            @php
              $tonHN = $tonThucMap[$sp->Ma_san_pham]['HN'] ?? 0;
              $tonHCM = $tonThucMap[$sp->Ma_san_pham]['HCM'] ?? 0;
              $tongTon = $tonHN + $tonHCM;
              $giaTriTon = $tongTon * (float)$sp->Gia_von_binh_quan;
            @endphp
            <tr style="border-top: 1px solid #F0E6E3;">
              <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $sp->Ma_san_pham }}</td>
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                {{ $sp->Ten_san_pham }}
                <div style="font-size: 12px; color: #6B5559; font-weight: 400;">
                  Niên vụ: {{ $sp->Nien_vu ?? 'NV' }} • Dung tích: {{ $sp->Dung_tich }} ml
                </div>
              </td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Loai_vang }}</td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $tonHN > 0 ? '#241417' : '#9CA3AF' }};">
                {{ number_format($tonHN, 0, ',', '.') }}
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $tonHCM > 0 ? '#241417' : '#9CA3AF' }};">
                {{ number_format($tonHCM, 0, ',', '.') }}
              </td>
              <td style="padding: 12px 16px; text-align: right; color: #6B5559;">
                {{ $sp->Gia_niem_yet !== null ? number_format($sp->Gia_niem_yet, 0, ',', '.') . ' đ' : '—' }}
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: #8B1E2D;">
                {{ number_format($sp->Gia_von_binh_quan, 0, ',', '.') }} đ
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">
                {{ number_format($giaTriTon, 0, ',', '.') }} đ
              </td>
              <td style="padding: 12px 16px;">
                @if($sp->Trang_thai === 'Đang kinh doanh')
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                    Đang kinh doanh
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #F3F4F6; color: #6B7280;">
                    Ngừng
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="padding: 30px; text-align: center; color: #6B5559;">Không có sản phẩm nào.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($sanPhams->hasPages())
      <div style="padding: 14px 18px; border-top: 1px solid #EADFDC;">
        {{ $sanPhams->links() }}
      </div>
    @endif
  </div>

  <div style="font-size: 13px; color: #6B5559;">
    Chỉ xem. Thêm mới và điều chỉnh thông tin kỹ thuật do Quản lý kho thực hiện.
  </div>
</div>
@endsection

