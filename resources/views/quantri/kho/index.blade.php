@extends('layouts.app')

@section('title', 'Quản lý kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Nút Thêm mới --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Quản trị hệ thống</span> &nbsp;/&nbsp; <strong style="color: #241417;">Kho</strong>
      </div>
      <h1 style="font-size: 24px; font-weight: 700; color: #241417;">Danh mục kho</h1>
    </div>

    <button type="button" onclick="openCreateModal();" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Thêm mới</span>
    </button>
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

  {{-- Bảng danh sách kho --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow: hidden;">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
        <thead>
          <tr style="background: #FAF5F3; color: #6B5559; border-bottom: 1px solid #EADFDC; font-size: 13px;">
            <th style="padding: 12px 20px; font-weight: 600;">Mã kho</th>
            <th style="padding: 12px 20px; font-weight: 600;">Tên kho</th>
            <th style="padding: 12px 20px; font-weight: 600;">Địa chỉ</th>
            <th style="padding: 12px 20px; font-weight: 600;">Nhân sự</th>
            <th style="padding: 12px 20px; font-weight: 600;">Trạng thái</th>
            <th style="padding: 12px 20px; font-weight: 600; text-align: right;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @forelse($khos as $k)
            <tr style="border-bottom: 1px solid #F3EAE8;">
              <td style="padding: 14px 20px; font-weight: 600; color: #6E1423; font-family: monospace; font-size: 13px;">
                {{ $k->Ma_kho }}
              </td>
              <td style="padding: 14px 20px; font-weight: 600; color: #241417;">
                {{ $k->Ten_kho }}
              </td>
              <td style="padding: 14px 20px; color: #6B5559;">
                {{ $k->Dia_chi }}
              </td>
              <td style="padding: 14px 20px; color: #6B5559;">
                {{ $k->nguoi_dung_count ?? 0 }} người dùng
              </td>
              <td style="padding: 14px 20px;">
                @if($k->Trang_thai === 'Hoạt động')
                  <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #0E9F6E;"></span>
                    Hoạt động
                  </span>
                @else
                  <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #F3F4F6; color: #6B7280;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #9CA3AF;"></span>
                    Ngừng
                  </span>
                @endif
              </td>
              <td style="padding: 14px 20px; text-align: right;">
                <button type="button" 
                  onclick="openEditModal('{{ $k->Ma_kho }}', '{{ addslashes($k->Ten_kho) }}', '{{ addslashes($k->Dia_chi) }}', '{{ $k->Trang_thai }}');"
                  style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 6px; border: 1px solid #EADFDC; background: #FFFFFF; color: #8B1E2D; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease;"
                  onmouseover="this.style.background='#8B1E2D';this.style.color='#FFFFFF';this.style.borderColor='#8B1E2D';"
                  onmouseout="this.style.background='#FFFFFF';this.style.color='#8B1E2D';this.style.borderColor='#EADFDC';"
                >
                  Sửa
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="padding: 24px; text-align: center; color: #6B5559;">
                Chưa có kho hàng nào trong hệ thống.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- MODAL THÊM / SỬA KHO --}}
<div id="kho-modal" style="display: none; position: fixed; inset: 0; background: rgba(36, 20, 23, 0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #EADFDC; width: 100%; max-width: 520px; padding: 24px 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #EADFDC;">
      <h3 id="modal-title" style="font-size: 18px; font-weight: 700; color: #241417; margin: 0;">Sửa kho</h3>
      <button type="button" onclick="closeModal();" style="border: 0; background: transparent; font-size: 20px; cursor: pointer; color: #6B5559;">&times;</button>
    </div>

    <form id="kho-form" method="POST" action="{{ route('quantri.kho.store') }}" style="display: flex; flex-direction: column; gap: 16px;">
      @csrf
      
      {{-- Mã kho --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Mã kho <span style="color: #C81E1E;">*</span>
        </label>
        <input type="text" id="form-ma-kho" name="Ma_kho" placeholder="Ví dụ: HN, HCM" required style="width: 100%; height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px; text-transform: uppercase;">
        <span id="ma-kho-note" style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Không sửa được sau khi tạo.</span>
      </div>

      {{-- Tên kho --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Tên kho <span style="color: #C81E1E;">*</span>
        </label>
        <input type="text" id="form-ten-kho" name="Ten_kho" placeholder="Ví dụ: Kho Hà Nội" required style="width: 100%; height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px;">
      </div>

      {{-- Địa chỉ --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Địa chỉ kho <span style="color: #C81E1E;">*</span>
        </label>
        <input type="text" id="form-dia-chi" name="Dia_chi" placeholder="Nhập địa chỉ cụ thể của kho" required style="width: 100%; height: 40px; padding: 0 12px; border-radius: 8px; border: 1px solid #EADFDC; font: inherit; font-size: 14px;">
      </div>

      {{-- Trạng thái --}}
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #241417; margin-bottom: 6px;">
          Trạng thái hoạt động <span style="color: #C81E1E;">*</span>
        </label>
        <div style="display: flex; gap: 20px;">
          <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
            <input type="radio" id="form-status-active" name="Trang_thai" value="Hoạt động" checked style="accent-color: #8B1E2D;">
            <span>Hoạt động</span>
          </label>
          <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
            <input type="radio" id="form-status-inactive" name="Trang_thai" value="Ngừng" style="accent-color: #8B1E2D;">
            <span>Ngừng</span>
          </label>
        </div>
        <span style="display: block; font-size: 11px; color: #8C787B; margin-top: 4px;">Kho Ngừng hoạt động không được chọn khi lập chứng từ mới hoặc gán người dùng.</span>
      </div>

      {{-- Buttons --}}
      <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 10px; padding-top: 16px; border-top: 1px solid #EADFDC;">
        <button type="button" onclick="closeModal();" style="height: 40px; padding: 0 18px; border-radius: 8px; border: 1px solid #EADFDC; background: #FFFFFF; color: #6B5559; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Hủy bỏ
        </button>

        <button type="submit" style="height: 40px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Lưu
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openCreateModal() {
    document.getElementById('modal-title').innerText = 'Thêm kho mới';
    document.getElementById('kho-form').action = "{{ route('quantri.kho.store') }}";
    
    const maInput = document.getElementById('form-ma-kho');
    maInput.value = '';
    maInput.readOnly = false;
    maInput.style.background = '#FFFFFF';

    document.getElementById('form-ten-kho').value = '';
    document.getElementById('form-dia-chi').value = '';
    document.getElementById('form-status-active').checked = true;

    document.getElementById('kho-modal').style.display = 'flex';
  }

  function openEditModal(ma, ten, diaChi, trangThai) {
    document.getElementById('modal-title').innerText = 'Sửa thông tin kho: ' + ma;
    document.getElementById('kho-form').action = "{{ url('/quan-tri/kho') }}/" + ma;

    const maInput = document.getElementById('form-ma-kho');
    maInput.value = ma;
    maInput.readOnly = true;
    maInput.style.background = '#F8F5F4';

    document.getElementById('form-ten-kho').value = ten;
    document.getElementById('form-dia-chi').value = diaChi;
    
    if (trangThai === 'Hoạt động') {
      document.getElementById('form-status-active').checked = true;
    } else {
      document.getElementById('form-status-inactive').checked = true;
    }

    document.getElementById('kho-modal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('kho-modal').style.display = 'none';
  }
</script>
@endsection

