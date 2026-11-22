@extends('layouts.app')

@section('title', 'Nhập kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
      <div style="font-size: 13px; color: #6B5559;">
        <span>Nhập kho</span> &nbsp;/&nbsp; <a href="{{ route('nv-kho.phieu-nhap.index') }}" style="color: #8B1E2D; text-decoration: none;">Phiếu nhập kho</a> &nbsp;/&nbsp; <strong style="color: #241417;">Nhập mới</strong>
      </div>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Nhập kho</h1>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Chọn loại nhập, chọn chứng từ gốc rồi nhập số lượng thực nhận.</p>
    </div>
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

  {{-- Tabs Loại nhập --}}
  <div role="tablist" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $loaiTabs = [
        'Nhập từ NCC' => 'Nhập từ NCC',
        'Khách trả hàng' => 'Khách trả hàng',
        'Hoàn giao thất bại' => 'Hoàn giao thất bại',
      ];
    @endphp

    @foreach($loaiTabs as $key => $label)
      @php $isActive = ($loai === $key); @endphp
      <a href="{{ route('nv-kho.nhap-kho.index', ['loai' => $key]) }}"
         role="tab"
         style="display: inline-flex; align-items: center; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; {{ $isActive ? 'background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  {{-- ============================================================== --}}
  {{-- TAB 1: NHẬP TỪ NHÀ CUNG CẤP                                    --}}
  {{-- ============================================================== --}}
  @if($loai === 'Nhập từ NCC')
    <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
      <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">Đơn đặt hàng đang chờ nhận</h2>

      <form method="GET" action="{{ route('nv-kho.nhap-kho.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
        <input type="hidden" name="loai" value="Nhập từ NCC">
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 1 1 280px;">
          <span>Tìm đơn</span>
          <input type="search" name="search" value="{{ $search }}" placeholder="Mã đơn, nhà cung cấp..."
                 style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>
        <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Tìm
        </button>
      </form>

      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px; text-align: left;">
          <thead>
            <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Mã đơn</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Nhà cung cấp</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Dự kiến nhận đủ</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đã nhận / Đặt</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Số lần đã giao</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;"></th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orders as $o)
              @php
                $tongDat = $o->tong_so_luong_dat;
                $daNhan = $o->tong_da_nhan;
                $soLanGiao = \App\Models\PhieuNhapKho::where('Ma_don_dat_hang', $o->Ma_don_dat_hang)->where('Trang_thai', 'Đã nhập kho')->count();
              @endphp
              <tr style="border-top: 1px solid #F0E6E3; transition: background 0.15s ease;" onmouseover="this.style.background='#FDFBFB';" onmouseout="this.style.background='transparent';">
                <td style="padding: 12px 16px; font-weight: 600; color: #241417;">{{ $o->Ma_don_dat_hang }}</td>
                <td style="padding: 12px 16px; color: #241417;">{{ $o->nhaCungCap->Ten_ncc ?? $o->Ma_ncc }}</td>
                <td style="padding: 12px 16px; color: #4A3A3D;">{{ $o->Ngay_du_kien_nhan_du ? date('d/m/Y', strtotime($o->Ngay_du_kien_nhan_du)) : '—' }}</td>
                <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: #241417;">{{ number_format($daNhan) }} / {{ number_format($tongDat) }}</td>
                <td style="padding: 12px 16px; text-align: right; color: #4A3A3D;">{{ $soLanGiao }} đợt</td>
                <td style="padding: 12px 16px;">
                  @if($o->can_xem_xet)
                    <span style="display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; border: 1px solid #B45309; color: #8A4B08; background: #FFFDF9;">
                      Cần xem xét
                    </span>
                  @endif
                </td>
                <td style="padding: 12px 16px; text-align: right;">
                  <a href="{{ route('nv-kho.nhap-kho.ncc', $o->Ma_don_dat_hang) }}"
                     style="display: inline-flex; align-items: center; height: 38px; padding: 0 16px; border-radius: 8px; background: #8B1E2D; color: #FFFFFF; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.15s ease;"
                     onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
                    Nhập hàng
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" style="padding: 36px 16px; text-align: center; color: #6B5559;">
                  Không có đơn đặt hàng nào đang chờ nhận.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

  {{-- ============================================================== --}}
  {{-- TAB 2 & 3: KHÁCH TRẢ HÀNG & HOÀN GIAO THẤT BẠI                 --}}
  {{-- ============================================================== --}}
  @else
    @php
      $slips = ($loai === 'Khách trả hàng') ? $slipsKhach : $slipsHoan;
      $isKhach = ($loai === 'Khách trả hàng');
    @endphp

    <section style="display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
      <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">
        {{ $isKhach ? '1. Tìm phiếu xuất của đơn khách trả' : '1. Chọn phiếu xuất giao thất bại chưa xử lý' }}
      </h2>

      <form method="GET" action="{{ route('nv-kho.nhap-kho.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
        <input type="hidden" name="loai" value="{{ $loai }}">
        <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; flex: 2 1 280px;">
          <span>Tìm theo mã đơn bán hoặc khách hàng</span>
          <input type="search" name="search" value="{{ $search }}" placeholder="Mã đơn bán, tên hoặc SĐT khách hàng..."
                 style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
        </label>
        <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          Tìm
        </button>
      </form>

      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px; text-align: left;">
          <thead>
            <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; width: 48px;">Chọn</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Phiếu xuất</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Đơn bán</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Khách hàng</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Ngày xuất kho</th>
              <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">{{ $isKhach ? 'Kết quả giao' : 'Lý do giao thất bại' }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($slips as $idx => $s)
              @php
                $db = $s->donHangBan;
                $kh = $db?->khachHang;
                $ctJson = json_encode($s->chiTiets->map(function($c) {
                  return [
                    'ma_lo' => $c->Ma_lo_thuc_xuat ?: $c->Ma_lo_phan_bo,
                    'ten_san_pham' => $c->lo?->sanPham?->Ten_san_pham ?? 'Sản phẩm',
                    'han_su_dung' => $c->lo?->Han_su_dung ? date('d/m/Y', strtotime($c->lo->Han_su_dung)) : '—',
                    'so_luong_xuat' => (int) $c->So_luong,
                  ];
                }));
              @endphp
              <tr style="border-top: 1px solid #F0E6E3; cursor: pointer; transition: background 0.15s ease;"
                  onclick="selectSlip('{{ $s->Ma_phieu_xuat }}', {{ $ctJson }}); document.getElementById('radio_{{ $s->Ma_phieu_xuat }}').checked = true;">
                <td style="padding: 12px 16px;">
                  <input type="radio" id="radio_{{ $s->Ma_phieu_xuat }}" name="chosen_px" value="{{ $s->Ma_phieu_xuat }}"
                         style="width: 18px; height: 18px; accent-color: #8B1E2D;"
                         onchange="selectSlip('{{ $s->Ma_phieu_xuat }}', {{ $ctJson }})">
                </td>
                <td style="padding: 12px 16px; font-weight: 600; color: #241417;">{{ $s->Ma_phieu_xuat }}</td>
                <td style="padding: 12px 16px; color: #241417;">{{ $s->Ma_don_hang_ban }}</td>
                <td style="padding: 12px 16px; color: #4A3A3D;">{{ $kh->Ten_khach_hang ?? '—' }}</td>
                <td style="padding: 12px 16px; color: #4A3A3D;">{{ $s->Thoi_diem_xuat_kho ? date('d/m/Y', strtotime($s->Thoi_diem_xuat_kho)) : '—' }}</td>
                <td style="padding: 12px 16px; color: #3B2A2D; font-size: 13px;">
                  {{ $isKhach ? 'Giao thành công ' . ($s->Thoi_diem_ket_qua_giao ? date('d/m/Y', strtotime($s->Thoi_diem_ket_qua_giao)) : '') : ($s->Ly_do_giao_that_bai ?: 'Giao hàng thất bại') }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="padding: 36px 16px; text-align: center; color: #6B5559;">
                  Không có phiếu xuất nào phù hợp điều kiện.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- Phần 2: Xử lý hàng hoàn --}}
    <form id="xuLyHoanForm" method="POST" action="{{ route('nv-kho.nhap-kho.xu-ly-hoan') }}" style="display: none; flex-direction: column; gap: 16px;">
      @csrf
      <input type="hidden" name="loai_nhap" value="{{ $loai }}">
      <input type="hidden" id="form_ma_phieu_xuat" name="ma_phieu_xuat" value="">

      <section style="display: flex; flex-direction: column; gap: 16px; padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h2 id="sectionTitle" style="margin: 0; font-size: 16px; font-weight: 600; color: #241417;">2. Xử lý hàng của phiếu xuất</h2>

        {{-- Radio Group chọn cách xử lý --}}
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
          <label style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; border: 2px solid #8B1E2D; background: #FCF7F6; color: #6E1423; font-weight: 600; cursor: pointer;" id="labelModeIn">
            <input type="radio" name="mode" value="in" checked onchange="toggleMode('in')" style="accent-color: #8B1E2D;">
            <span>Nhập lại kho</span>
          </label>

          <label style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417; cursor: pointer;" id="labelModeOut">
            <input type="radio" name="mode" value="out" onchange="toggleMode('out')" style="accent-color: #8B1E2D;">
            <span>Không nhập kho</span>
          </label>
        </div>

        {{-- Khối Nhập lại kho --}}
        <div id="blockModeIn" style="display: flex; flex-direction: column; gap: 14px;">
          <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 760px; border-collapse: collapse; font-size: 14px; text-align: left;">
              <thead>
                <tr style="background: #F6ECEA; border-bottom: 1px solid #EADFDC;">
                  <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Sản phẩm</th>
                  <th style="padding: 12px 16px; font-weight: 600; color: #5E1320;">Lô</th>
                  <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">Đã xuất</th>
                  <th style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right;">SL thực nhận</th>
                </tr>
              </thead>
              <tbody id="retLinesBody">
                {{-- Dynamic rows inserted via JS --}}
              </tbody>
            </table>
          </div>

          <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; color: #241417;">
            <span>{{ $isKhach ? 'Lý do khách trả hàng' : 'Lý do hàng hoàn về' }} <strong style="color: #8B1E2D;">*</strong></span>
            <textarea name="ly_do_tra_hang" rows="2" placeholder="Nhập lý do chi tiết..."
                      style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
          </label>

          <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; color: #241417;">
            <span>Ghi chú xử lý hoàn (bắt buộc nếu nhận lại ít hơn số đã xuất)</span>
            <textarea name="ghi_chu_xu_ly_hoan" rows="2" placeholder="Bắt buộc ghi rõ nếu nhận lại ít hơn số đã xuất. VD: 2 chai vỡ khi vận chuyển về"
                      style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
          </label>

          <span style="font-size: 13px; color: #6B5559;">
            * Hàng nhập lại vào đúng lô đã xuất. Số thực nhận không vượt số đã xuất. Phần chênh giữa số đã xuất và số nhận lại được tính là hàng mất, không tạo chứng từ riêng.
          </span>

          <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
            <button type="submit"
                    style="height: 44px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
              Xác nhận nhập kho
            </button>
          </div>
        </div>

        {{-- Khối Không nhập kho --}}
        <div id="blockModeOut" style="display: none; flex-direction: column; gap: 14px;">
          <label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 500; color: #241417;">
            <span>Lý do / Ghi chú không nhập kho <strong style="color: #8B1E2D;">*</strong></span>
            <textarea id="out_ghi_chu" name="out_ghi_chu" rows="3" placeholder="Ghi rõ lý do không thể nhập lại kho (hàng hỏng toàn bộ, khách không trả lại hàng, v.v.)..."
                      style="padding: 10px 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; resize: vertical;"></textarea>
          </label>

          <div style="display: flex; flex-direction: column; gap: 6px; padding: 14px 18px; border-radius: 10px; background: #FDF6EA; border: 1px solid #F0D9B0; font-size: 13px; color: #3B2A2D;">
            <strong>Khi bấm "Xác nhận không nhập kho":</strong>
            <span>• Không tạo phiếu nhập, tồn kho không thay đổi.</span>
            <span>• Phiếu xuất được ghi "Đã xử lý – không nhập kho" kèm lý do, thời điểm và người xử lý.</span>
            <span>• Phiếu xuất biến khỏi danh sách chờ xử lý hoàn và không xử lý lại được.</span>
          </div>

          <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
            <button type="submit" onclick="copyOutNote()"
                    style="height: 44px; padding: 0 22px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
              Xác nhận không nhập kho
            </button>
          </div>
        </div>
      </section>
    </form>

    <script>
      function selectSlip(maPx, items) {
        document.getElementById('xuLyHoanForm').style.display = 'flex';
        document.getElementById('form_ma_phieu_xuat').value = maPx;
        document.getElementById('sectionTitle').innerText = '2. Xử lý hàng của phiếu xuất ' + maPx;

        var tbody = document.getElementById('retLinesBody');
        tbody.innerHTML = '';

        items.forEach(function(it, idx) {
          var tr = document.createElement('tr');
          tr.style.borderTop = '1px solid #F0E6E3';
          tr.innerHTML =
            '<td style="padding: 12px 16px; font-weight: 500; color: #241417;">' + it.ten_san_pham + '</td>' +
            '<td style="padding: 12px 16px; color: #4A3A3D;">' + it.ma_lo + ' · HSD ' + it.han_su_dung + '</td>' +
            '<td style="padding: 12px 16px; text-align: right; color: #4A3A3D;">' + it.so_luong_xuat + '</td>' +
            '<td style="padding: 12px 16px; text-align: right;">' +
              '<input type="hidden" name="items[' + idx + '][ma_lo]" value="' + it.ma_lo + '">' +
              '<input type="number" name="items[' + idx + '][so_luong_nhap]" value="' + it.so_luong_xuat + '" min="0" max="' + it.so_luong_xuat + '" ' +
                     'style="height: 38px; width: 90px; padding: 0 8px; border-radius: 6px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; text-align: right;">' +
            '</td>';
          tbody.appendChild(tr);
        });
      }

      function toggleMode(mode) {
        if (mode === 'in') {
          document.getElementById('blockModeIn').style.display = 'flex';
          document.getElementById('blockModeOut').style.display = 'none';
          document.getElementById('labelModeIn').style.border = '2px solid #8B1E2D';
          document.getElementById('labelModeIn').style.background = '#FCF7F6';
          document.getElementById('labelModeIn').style.color = '#6E1423';
          document.getElementById('labelModeOut').style.border = '1px solid #DCCAC6';
          document.getElementById('labelModeOut').style.background = '#FFFFFF';
          document.getElementById('labelModeOut').style.color = '#241417';
        } else {
          document.getElementById('blockModeIn').style.display = 'none';
          document.getElementById('blockModeOut').style.display = 'flex';
          document.getElementById('labelModeOut').style.border = '2px solid #8B1E2D';
          document.getElementById('labelModeOut').style.background = '#FCF7F6';
          document.getElementById('labelModeOut').style.color = '#6E1423';
          document.getElementById('labelModeIn').style.border = '1px solid #DCCAC6';
          document.getElementById('labelModeIn').style.background = '#FFFFFF';
          document.getElementById('labelModeIn').style.color = '#241417';
        }
      }

      function copyOutNote() {
        var note = document.getElementById('out_ghi_chu').value;
        var hiddenInput = document.querySelector('textarea[name="ghi_chu_xu_ly_hoan"]');
        if (hiddenInput) {
          hiddenInput.value = note;
        }
      }
    </script>
  @endif
</div>
@endsection

