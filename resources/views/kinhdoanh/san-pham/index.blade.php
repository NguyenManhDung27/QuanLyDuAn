@extends('layouts.app')

@section('title', 'Sản phẩm và giá niêm yết – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Sản phẩm và giá</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Sản phẩm và giá niêm yết</h1>
      <p style="margin: 4px 0 0 0; font-size: 14px; color: #6B5559;">
        Xem sản phẩm, tồn khả dụng từng kho và cập nhật giá niêm yết. Thông tin quy cách do Quản lý kho quản lý.
      </p>
    </div>
  </div>

  {{-- Thông báo Alerts --}}
  @if(session('success'))
    <div style="padding: 12px 18px; border-radius: 8px; background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; font-size: 14px;">
      {{ session('success') }}
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

  {{-- Thanh tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('kinh-doanh.san-pham.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 240px;">
      Tìm kiếm
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên sản phẩm, nhà sản xuất" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 130px;">
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

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 130px;">
      Quốc gia
      <select name="quoc_gia" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        @foreach($dsQuocGia as $qg)
          <option value="{{ $qg }}" {{ request('quoc_gia') == $qg ? 'selected' : '' }}>{{ $qg }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; align-items: center; gap: 8px; height: 42px; font-size: 13px; font-weight: 500; color: #241417; cursor: pointer;">
      <input type="checkbox" name="chua_co_gia" value="1" {{ request('chua_co_gia') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #8B1E2D;">
      <span>Chỉ hiện sản phẩm chưa có giá</span>
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('kinh-doanh.san-pham.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bảng danh sách Sản phẩm & Giá niêm yết (theo đúng mockup KD-06) --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; min-width: 960px; border-collapse: collapse; font-size: 14px; text-align: left;">
        <thead>
          <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; vertical-align: middle;">Mã SP</th>
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; vertical-align: middle;">Sản phẩm</th>
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; vertical-align: middle;">Niên vụ</th>
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; vertical-align: middle;">Dung tích</th>
            <th colspan="2" style="padding: 8px 16px 4px; font-weight: 600; text-align: center; border-bottom: 1px solid #E7D6D2;">Tồn khả dụng</th>
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; text-align: right; vertical-align: middle;">Giá niêm yết (chưa thuế)</th>
            <th rowspan="2" style="padding: 12px 16px; font-weight: 600; text-align: center; vertical-align: middle; width: 150px;">Thao tác</th>
          </tr>
          <tr style="background: #F6ECEA; color: #5E1320;">
            <th style="padding: 4px 16px 8px; font-weight: 600; font-size: 13px; text-align: right;">Hà Nội</th>
            <th style="padding: 4px 16px 8px; font-weight: 600; font-size: 13px; text-align: right;">TP.HCM</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sanPhams as $sp)
            @php
              $isEditing = ($editingMa === $sp->Ma_san_pham);
              $kdHN = $tonKhaDungMap[$sp->Ma_san_pham]['HN'] ?? 0;
              $kdHCM = $tonKhaDungMap[$sp->Ma_san_pham]['HCM'] ?? 0;
            @endphp
            <tr style="border-top: 1px solid #F0E6E3; {{ $isEditing ? 'background: #FFF9F8;' : '' }}">
              <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $sp->Ma_san_pham }}</td>
              <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                {{ $sp->Ten_san_pham }}
                <div style="font-size: 12px; color: #6B5559; font-weight: 400;">{{ $sp->Loai_vang }} • {{ $sp->Quoc_gia }}</div>
              </td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Nien_vu ?? 'NV' }}</td>
              <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Dung_tich }} ml</td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $kdHN > 0 ? '#241417' : '#9CA3AF' }};">
                {{ number_format($kdHN, 0, ',', '.') }}
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $kdHCM > 0 ? '#241417' : '#9CA3AF' }};">
                {{ number_format($kdHCM, 0, ',', '.') }}
              </td>

              {{-- Cột Giá niêm yết: Xem hoặc Sửa --}}
              <td style="padding: 12px 16px; text-align: right;">
                @if($isEditing)
                  <form id="price-form-{{ $sp->Ma_san_pham }}" method="POST" action="{{ route('kinh-doanh.san-pham.update-gia', $sp->Ma_san_pham) }}" style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                    @csrf
                    <input type="number" name="Gia_niem_yet" value="{{ $sp->Gia_niem_yet ? (int)$sp->Gia_niem_yet : '' }}" required min="0" step="1000" placeholder="VNĐ" style="height: 36px; width: 140px; padding: 0 10px; border-radius: 6px; border: 1px solid #8B1E2D; font: inherit; font-size: 14px; text-align: right; color: #241417; background: #FFFFFF;">
                  </form>
                @else
                  @if($sp->Gia_niem_yet !== null)
                    <span style="font-weight: 600; color: #241417;">
                      {{ number_format($sp->Gia_niem_yet, 0, ',', '.') }} đ
                    </span>
                  @else
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #FEF3C7; color: #92400E;">
                      Chưa có giá
                    </span>
                  @endif
                @endif
              </td>

              {{-- Cột Thao tác --}}
              <td style="padding: 12px 16px; text-align: center;">
                @if($isEditing)
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="submit" form="price-form-{{ $sp->Ma_san_pham }}" style="height: 34px; padding: 0 12px; border-radius: 6px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
                      Lưu
                    </button>
                    <a href="{{ route('kinh-doanh.san-pham.index', request()->except('edit')) }}" style="display: inline-flex; align-items: center; height: 34px; padding: 0 10px; border-radius: 6px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 13px; text-decoration: none;">
                      Hủy
                    </a>
                  </div>
                @else
                  <a href="{{ route('kinh-doanh.san-pham.index', array_merge(request()->query(), ['edit' => $sp->Ma_san_pham])) }}" style="display: inline-flex; align-items: center; height: 34px; padding: 0 12px; border-radius: 6px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none;">
                    Sửa giá
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding: 30px; text-align: center; color: #6B5559;">Không có sản phẩm nào.</td>
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

  <div style="font-size: 13px; color: #6B5559; padding: 0 4px;">
    Giá mới chỉ áp dụng cho đơn lập sau khi lưu; đơn đã xác nhận giữ nguyên giá lúc lập. Sản phẩm chưa có giá niêm yết thì không chọn được khi lập đơn.
  </div>
</div>
@endsection

