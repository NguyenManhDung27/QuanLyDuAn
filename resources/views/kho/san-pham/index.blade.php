@extends('layouts.app')

@section('title', 'Quản lý sản phẩm – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Nút Thêm mới --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Sản phẩm</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Sản phẩm</h1>
    </div>

    <a href="{{ route('kho.san-pham.index') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Thêm sản phẩm</span>
    </a>
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

  {{-- Thanh tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('kho.san-pham.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 220px;">
      Tìm kiếm
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên sản phẩm, nhà sản xuất" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 130px;">
      Loại vang
      <select name="loai_vang" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả" {{ request('loai_vang') == 'Tất cả' ? 'selected' : '' }}>Tất cả</option>
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

    <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 150px;">
      Trạng thái
      <select name="trang_thai" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        <option value="Tất cả">Tất cả</option>
        <option value="Đang kinh doanh" {{ request('trang_thai') == 'Đang kinh doanh' ? 'selected' : '' }}>Đang kinh doanh</option>
        <option value="Ngừng" {{ request('trang_thai') == 'Ngừng' ? 'selected' : '' }}>Ngừng</option>
      </select>
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Lọc</button>
    <a href="{{ route('kho.san-pham.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bố cục 2 cột: Bảng danh sách (trái) + Form Thêm/Sửa (phải) --}}
  <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
    {{-- Cột Trái: Bảng danh sách --}}
    <div style="flex: 3 1 540px; min-width: 0; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 620px; border-collapse: collapse; font-size: 14px; text-align: left;">
          <thead>
            <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
              <th style="padding: 12px 16px; font-weight: 600;">Mã SP</th>
              <th style="padding: 12px 16px; font-weight: 600;">Tên sản phẩm</th>
              <th style="padding: 12px 16px; font-weight: 600;">Loại</th>
              <th style="padding: 12px 16px; font-weight: 600;">Quốc gia</th>
              <th style="padding: 12px 16px; font-weight: 600;">Niên vụ</th>
              <th style="padding: 12px 16px; font-weight: 600;">Dung tích</th>
              <th style="padding: 12px 16px; font-weight: 600;">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sanPhams as $sp)
              @php
                $isSelected = $selectedSp && $selectedSp->Ma_san_pham === $sp->Ma_san_pham;
              @endphp
              <tr onclick="window.location='{{ route('kho.san-pham.index', array_merge(request()->query(), ['ma' => $sp->Ma_san_pham])) }}';"
                  style="border-top: 1px solid #F0E6E3; cursor: pointer; transition: background 0.15s ease; {{ $isSelected ? 'background: #FAF0EE;' : '' }}"
                  onmouseover="if(!{{ $isSelected ? 1 : 0 }}) this.style.background='#FAF5F3';"
                  onmouseout="if(!{{ $isSelected ? 1 : 0 }}) this.style.background='transparent';">
                <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $sp->Ma_san_pham }}</td>
                <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                  {{ $sp->Ten_san_pham }}
                  @if($isSelected)
                    <span style="font-size: 11px; color: #8B1E2D; font-weight: 600; margin-left: 4px;">● đang chọn</span>
                  @endif
                </td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Loai_vang }}</td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Quoc_gia }}</td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Nien_vu ?? 'NV' }}</td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $sp->Dung_tich }} ml</td>
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
                <td colspan="7" style="padding: 30px; text-align: center; color: #6B5559;">Không tìm thấy sản phẩm nào.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Phân trang --}}
      @if($sanPhams->hasPages())
        <div style="padding: 14px 18px; border-top: 1px solid #EADFDC;">
          {{ $sanPhams->links() }}
        </div>
      @endif
    </div>

    {{-- Cột Phải: Form Thêm / Sửa Sản phẩm --}}
    <div style="flex: 2 1 360px; display: flex; flex-direction: column; gap: 14px; padding: 22px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #241417;">
          {{ $selectedSp ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới' }}
        </h2>
        @if($selectedSp)
          <a href="{{ route('kho.san-pham.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500;">+ Tạo mới</a>
        @endif
      </div>

      <form method="POST" action="{{ $selectedSp ? route('kho.san-pham.update', $selectedSp->Ma_san_pham) : route('kho.san-pham.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        @if($selectedSp)
          @method('PUT')
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Mã sản phẩm
            <input type="text" value="{{ $selectedSp ? $selectedSp->Ma_san_pham : $nextCode }}" readonly style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559; font-family: monospace;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Trạng thái *
            <select name="Trang_thai" required style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
              <option value="Đang kinh doanh" {{ old('Trang_thai', $selectedSp?->Trang_thai) === 'Đang kinh doanh' ? 'selected' : '' }}>Đang kinh doanh</option>
              <option value="Ngừng" {{ old('Trang_thai', $selectedSp?->Trang_thai) === 'Ngừng' ? 'selected' : '' }}>Ngừng</option>
            </select>
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Tên sản phẩm *
          <input type="text" name="Ten_san_pham" value="{{ old('Ten_san_pham', $selectedSp?->Ten_san_pham) }}" required placeholder="VD: Château X Bordeaux Rouge" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Loại vang *
            <select name="Loai_vang" required style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
              <option value="Đỏ" {{ old('Loai_vang', $selectedSp?->Loai_vang) === 'Đỏ' ? 'selected' : '' }}>Đỏ</option>
              <option value="Trắng" {{ old('Loai_vang', $selectedSp?->Loai_vang) === 'Trắng' ? 'selected' : '' }}>Trắng</option>
              <option value="Hồng" {{ old('Loai_vang', $selectedSp?->Loai_vang) === 'Hồng' ? 'selected' : '' }}>Hồng</option>
              <option value="Vang nổ" {{ old('Loai_vang', $selectedSp?->Loai_vang) === 'Vang nổ' ? 'selected' : '' }}>Vang nổ</option>
              <option value="Khác" {{ old('Loai_vang', $selectedSp?->Loai_vang) === 'Khác' ? 'selected' : '' }}>Khác</option>
            </select>
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Niên vụ
            <input type="number" name="Nien_vu" value="{{ old('Nien_vu', $selectedSp?->Nien_vu) }}" placeholder="Để trống nếu NV" min="1900" max="2100" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Quốc gia *
            <input type="text" name="Quoc_gia" value="{{ old('Quoc_gia', $selectedSp?->Quoc_gia) }}" required placeholder="VD: Pháp" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Vùng
            <input type="text" name="Vung" value="{{ old('Vung', $selectedSp?->Vung) }}" placeholder="VD: Bordeaux" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Nhà sản xuất *
          <input type="text" name="Nha_san_xuat" value="{{ old('Nha_san_xuat', $selectedSp?->Nha_san_xuat) }}" required placeholder="Tên nhà sản xuất" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Dung tích (ml) *
            <input type="number" name="Dung_tich" value="{{ old('Dung_tich', $selectedSp?->Dung_tich ?? 750) }}" required min="1" placeholder="750" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Nồng độ (%)
            <input type="number" step="0.1" name="Nong_do_con" value="{{ old('Nong_do_con', $selectedSp?->Nong_do_con) }}" min="0" max="100" placeholder="13.5" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Đơn vị tính *
            <input type="text" name="Don_vi_tinh" value="{{ old('Don_vi_tinh', $selectedSp?->Don_vi_tinh ?? 'Chai') }}" required style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Chai / thùng
            <input type="number" name="Quy_cach_thung" value="{{ old('Quy_cach_thung', $selectedSp?->Quy_cach_thung) }}" min="1" placeholder="6" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Giá niêm yết
          <input type="text" value="{{ $selectedSp && $selectedSp->Gia_niem_yet !== null ? number_format($selectedSp->Gia_niem_yet, 0, ',', '.') . ' đ' : '[do Kinh doanh nhập]' }}" readonly style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559;">
          <span style="font-size: 12px; color: #6B5559;">Chỉ Kinh doanh sửa được.</span>
        </label>

        <span style="font-size: 12px; color: #6B5559; line-height: 1.4;">
          Chỉ xóa được khi sản phẩm chưa có lô, chứng từ. Sản phẩm đã phát sinh dữ liệu thì chuyển Ngừng.
        </span>

        <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 6px;">
          @if($selectedSp)
            <button type="button" onclick="if(confirm('Bạn có chắc chắn muốn xóa sản phẩm {{ $selectedSp->Ma_san_pham }} không?')) { document.getElementById('delete-sp-form').submit(); }" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #FDE8E8; color: #9B1C1C; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; margin-right: auto;">
              Xóa
            </button>
          @endif

          <a href="{{ route('kho.san-pham.index') }}" style="display: inline-flex; align-items: center; height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; text-decoration: none;">
            Hủy bỏ
          </a>

          <button type="submit" style="height: 40px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Lưu
          </button>
        </div>
      </form>

      @if($selectedSp)
        <form id="delete-sp-form" action="{{ route('kho.san-pham.destroy', $selectedSp->Ma_san_pham) }}" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      @endif
    </div>
  </div>
</div>
@endsection

