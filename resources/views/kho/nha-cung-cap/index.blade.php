@extends('layouts.app')

@section('title', 'Quản lý nhà cung cấp – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Nút Thêm mới --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Danh mục</span> &nbsp;/&nbsp; <strong style="color: #241417;">Nhà cung cấp</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Nhà cung cấp</h1>
    </div>

    <a href="{{ route('kho.nha-cung-cap.index') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Thêm nhà cung cấp</span>
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
  <form method="GET" action="{{ route('kho.nha-cung-cap.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
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
    <a href="{{ route('kho.nha-cung-cap.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; text-decoration: none;">Đặt lại</a>
  </form>

  {{-- Bố cục 2 cột: Bảng danh sách (trái) + Form Thêm/Sửa (phải) --}}
  <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
    {{-- Cột Trái: Bảng danh sách --}}
    <div style="flex: 3 1 520px; min-width: 0; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 620px; border-collapse: collapse; font-size: 14px; text-align: left;">
          <thead>
            <tr style="background: #F6ECEA; color: #5E1320; border-bottom: 1px solid #EADFDC;">
              <th style="padding: 12px 16px; font-weight: 600;">Mã NCC</th>
              <th style="padding: 12px 16px; font-weight: 600;">Tên nhà cung cấp</th>
              <th style="padding: 12px 16px; font-weight: 600;">Quốc gia</th>
              <th style="padding: 12px 16px; font-weight: 600;">Người liên hệ</th>
              <th style="padding: 12px 16px; font-weight: 600;">Điện thoại</th>
              <th style="padding: 12px 16px; font-weight: 600;">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @forelse($nhaCungCaps as $ncc)
              @php
                $isSelected = $selectedNcc && $selectedNcc->Ma_ncc === $ncc->Ma_ncc;
              @endphp
              <tr onclick="window.location='{{ route('kho.nha-cung-cap.index', array_merge(request()->query(), ['ma' => $ncc->Ma_ncc])) }}';"
                  style="border-top: 1px solid #F0E6E3; cursor: pointer; transition: background 0.15s ease; {{ $isSelected ? 'background: #FAF0EE;' : '' }}"
                  onmouseover="if(!{{ $isSelected ? 1 : 0 }}) this.style.background='#FAF5F3';"
                  onmouseout="if(!{{ $isSelected ? 1 : 0 }}) this.style.background='transparent';">
                <td style="padding: 12px 16px; font-weight: 600; color: #8B1E2D; font-family: monospace;">{{ $ncc->Ma_ncc }}</td>
                <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
                  {{ $ncc->Ten_ncc }}
                  @if($isSelected)
                    <span style="font-size: 11px; color: #8B1E2D; font-weight: 600; margin-left: 4px;">● đang chọn</span>
                  @endif
                </td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Quoc_gia }}</td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->Nguoi_lien_he ?? '—' }}</td>
                <td style="padding: 12px 16px; color: #6B5559;">{{ $ncc->So_dien_thoai ?? '—' }}</td>
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
                <td colspan="6" style="padding: 30px; text-align: center; color: #6B5559;">Không tìm thấy nhà cung cấp nào.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Phân trang --}}
      @if($nhaCungCaps->hasPages())
        <div style="padding: 14px 18px; border-top: 1px solid #EADFDC;">
          {{ $nhaCungCaps->links() }}
        </div>
      @endif
    </div>

    {{-- Cột Phải: Form Thêm / Sửa Nhà Cung Cấp --}}
    <div style="flex: 2 1 340px; display: flex; flex-direction: column; gap: 14px; padding: 22px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #241417;">
          {{ $selectedNcc ? 'Chỉnh sửa nhà cung cấp' : 'Thêm nhà cung cấp mới' }}
        </h2>
        @if($selectedNcc)
          <a href="{{ route('kho.nha-cung-cap.index') }}" style="font-size: 13px; color: #8B1E2D; text-decoration: none; font-weight: 500;">+ Tạo mới</a>
        @endif
      </div>

      <form method="POST" action="{{ $selectedNcc ? route('kho.nha-cung-cap.update', $selectedNcc->Ma_ncc) : route('kho.nha-cung-cap.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        @if($selectedNcc)
          @method('PUT')
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Mã NCC
            <input type="text" value="{{ $selectedNcc ? $selectedNcc->Ma_ncc : $nextCode }}" readonly style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #F6F1EF; color: #6B5559; font-family: monospace;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Trạng thái *
            <select name="Trang_thai" required style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
              <option value="Đang giao dịch" {{ old('Trang_thai', $selectedNcc?->Trang_thai) === 'Đang giao dịch' ? 'selected' : '' }}>Đang giao dịch</option>
              <option value="Ngừng giao dịch" {{ old('Trang_thai', $selectedNcc?->Trang_thai) === 'Ngừng giao dịch' ? 'selected' : '' }}>Ngừng giao dịch</option>
            </select>
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Tên nhà cung cấp *
          <input type="text" name="Ten_ncc" value="{{ old('Ten_ncc', $selectedNcc?->Ten_ncc) }}" required placeholder="Tên đầy đủ của nhà cung cấp" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Quốc gia *
            <input type="text" name="Quoc_gia" value="{{ old('Quoc_gia', $selectedNcc?->Quoc_gia) }}" required placeholder="VD: Pháp" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Mã số thuế
            <input type="text" name="Ma_so_thue" value="{{ old('Ma_so_thue', $selectedNcc?->Ma_so_thue) }}" placeholder="Mã số thuế" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Địa chỉ
          <input type="text" name="Dia_chi" value="{{ old('Dia_chi', $selectedNcc?->Dia_chi) }}" placeholder="Địa chỉ trụ sở/kho" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Người liên hệ
            <input type="text" name="Nguoi_lien_he" value="{{ old('Nguoi_lien_he', $selectedNcc?->Nguoi_lien_he) }}" placeholder="Họ tên người liên hệ" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>

          <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
            Số điện thoại
            <input type="tel" name="So_dien_thoai" value="{{ old('So_dien_thoai', $selectedNcc?->So_dien_thoai) }}" placeholder="Số điện thoại" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
          </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; font-weight: 500;">
          Email
          <input type="email" name="Email" value="{{ old('Email', $selectedNcc?->Email) }}" placeholder="Email liên hệ" style="height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>

        <span style="font-size: 12px; color: #6B5559; line-height: 1.4;">
          Nhà cung cấp Ngừng giao dịch không được chọn khi lập đề xuất mới. Chỉ xóa được khi chưa có đề xuất, đơn đặt hàng nào.
        </span>

        <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 6px;">
          @if($selectedNcc)
            <button type="button" onclick="if(confirm('Bạn có chắc chắn muốn xóa nhà cung cấp {{ $selectedNcc->Ma_ncc }} không?')) { document.getElementById('delete-ncc-form').submit(); }" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 0; background: #FDE8E8; color: #9B1C1C; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; margin-right: auto;">
              Xóa
            </button>
          @endif

          <a href="{{ route('kho.nha-cung-cap.index') }}" style="display: inline-flex; align-items: center; height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; font: inherit; font-size: 14px; text-decoration: none;">
            Hủy bỏ
          </a>

          <button type="submit" style="height: 40px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
            Lưu
          </button>
        </div>
      </form>

      @if($selectedNcc)
        <form id="delete-ncc-form" action="{{ route('kho.nha-cung-cap.destroy', $selectedNcc->Ma_ncc) }}" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      @endif
    </div>
  </div>
</div>
@endsection

