@extends('layouts.app')

@section('title', 'Theo dõi tồn kho – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
  {{-- Header: Breadcrumb & Title & Nút Tạo đề xuất nhập kho --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div>
      <div style="font-size: 13px; color: #6B5559; margin-bottom: 4px;">
        <span>Mua hàng</span> &nbsp;/&nbsp; <strong style="color: #241417;">Theo dõi tồn kho</strong>
      </div>
      <h1 style="font-size: 26px; font-weight: 700; color: #241417; margin: 0;">Theo dõi tồn kho</h1>
    </div>

    <a href="{{ route('kho.de-xuat.create') }}" style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: background 0.15s ease;" onmouseover="this.style.background='#6E1423';" onmouseout="this.style.background='#8B1E2D';">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>Tạo đề xuất nhập kho</span>
    </a>
  </div>

  {{-- Form tìm kiếm & bộ lọc --}}
  <form method="GET" action="{{ route('kho.ton-kho.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <label style="display: flex; flex-direction: column; gap: 6px; flex: 2 1 240px; font-size: 13px; font-weight: 500;">
      Tìm sản phẩm
      <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Mã, tên sản phẩm, nhà sản xuất" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 140px; font-size: 13px; font-weight: 500;">
      Loại vang
      <select name="loai_vang" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
        <option value="Tất cả" {{ $filters['loai_vang'] === 'Tất cả' ? 'selected' : '' }}>Tất cả</option>
        @foreach($loaiVangList as $lv)
          <option value="{{ $lv }}" {{ $filters['loai_vang'] === $lv ? 'selected' : '' }}>{{ $lv }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 140px; font-size: 13px; font-weight: 500;">
      Quốc gia
      <select name="quoc_gia" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF; color: #241417;">
        <option value="Tất cả" {{ $filters['quoc_gia'] === 'Tất cả' ? 'selected' : '' }}>Tất cả</option>
        @foreach($quocGiaList as $qg)
          <option value="{{ $qg }}" {{ $filters['quoc_gia'] === $qg ? 'selected' : '' }}>{{ $qg }}</option>
        @endforeach
      </select>
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 120px; font-size: 13px; font-weight: 500;">
      Niên vụ
      <input type="number" name="nien_vu" value="{{ $filters['nien_vu'] }}" placeholder="VD: 2018" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; color: #241417; background: #FFFFFF;">
    </label>

    <button type="submit" style="height: 42px; padding: 0 18px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#F6ECEA';" onmouseout="this.style.background='#FFFFFF';">
      Lọc
    </button>

    @if($filters['search'] || $filters['loai_vang'] !== 'Tất cả' || $filters['quoc_gia'] !== 'Tất cả' || $filters['nien_vu'])
      <a href="{{ route('kho.ton-kho.index') }}" style="display: inline-flex; align-items: center; height: 42px; padding: 0 14px; border-radius: 8px; border: 1px solid #DCCAC6; background: #FFFFFF; color: #6B5559; font-size: 14px; text-decoration: none;">
        Đặt lại
      </a>
    @endif
  </form>

  {{-- Chú thích các loại tồn kho --}}
  <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 13px; color: #3B2A2D; padding: 4px 4px;">
    <span><b style="color: #5E1320;">Tồn thực:</b> hàng đang có trong kho</span>
    <span><b style="color: #5E1320;">Tồn khả dụng:</b> tồn thực trừ hàng đang giữ cho đơn bán</span>
    <span><b style="color: #5E1320;">Tồn dự kiến:</b> tồn thực cộng hàng còn chờ nhận từ đơn đặt hàng</span>
  </div>

  {{-- Bảng danh sách sản phẩm và tồn kho --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <table style="width: 100%; min-width: 980px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA; text-align: left;">
          <th scope="col" rowspan="2" style="padding: 12px 16px; font-weight: 600; color: #5E1320; border-bottom: 1px solid #E7D6D2; width: 120px;">Mã SP</th>
          <th scope="col" rowspan="2" style="padding: 12px 16px; font-weight: 600; color: #5E1320; border-bottom: 1px solid #E7D6D2;">Sản phẩm</th>
          <th scope="col" rowspan="2" style="padding: 12px 16px; font-weight: 600; color: #5E1320; border-bottom: 1px solid #E7D6D2; width: 90px;">Niên vụ</th>
          <th scope="col" rowspan="2" style="padding: 12px 16px; font-weight: 600; color: #5E1320; border-bottom: 1px solid #E7D6D2; width: 100px;">Dung tích</th>
          <th scope="colgroup" colspan="3" style="padding: 10px 16px 4px; font-weight: 600; color: #5E1320; text-align: center; border-bottom: 1px solid #E7D6D2;">{{ $tenKho }}</th>
          <th scope="col" rowspan="2" style="padding: 12px 16px; font-weight: 600; color: #5E1320; text-align: right; border-bottom: 1px solid #E7D6D2; width: 170px;">Tồn thực toàn công ty</th>
        </tr>
        <tr style="background: #F6ECEA;">
          <th scope="col" style="padding: 6px 16px 10px; font-weight: 600; color: #5E1320; text-align: right; border-bottom: 1px solid #E7D6D2; width: 110px;">Tồn thực</th>
          <th scope="col" style="padding: 6px 16px 10px; font-weight: 600; color: #5E1320; text-align: right; border-bottom: 1px solid #E7D6D2; width: 110px;">Khả dụng</th>
          <th scope="col" style="padding: 6px 16px 10px; font-weight: 600; color: #5E1320; text-align: right; border-bottom: 1px solid #E7D6D2; width: 110px;">Dự kiến</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sanPhams as $sp)
          @php
            $maSp = $sp->Ma_san_pham;
            $tonThucKho = $tonThucMap[$maSp][$maKho] ?? 0;
            $tonKhaDungKho = $tonKhaDungMap[$maSp][$maKho] ?? 0;
            $tonDuKienKho = $tonDuKienMap[$maSp] ?? 0;
            $tonCongTy = array_sum($tonThucMap[$maSp] ?? []);
            $lots = $lotsMap[$maSp] ?? [];
            $hasLots = count($lots) > 0;
          @endphp
          {{-- Dòng sản phẩm cha (Click để mở/đóng chi tiết lô) --}}
          <tr id="parent-row-{{ $maSp }}" onclick="toggleLotRow('{{ $maSp }}')" style="border-top: 1px solid #F0E6E3; cursor: pointer; transition: background 0.15s ease;" onmouseover="if(!isRowOpen('{{ $maSp }}')) this.style.background='#FDFBFB';" onmouseout="if(!isRowOpen('{{ $maSp }}')) this.style.background='transparent';">
            <td style="padding: 12px 16px; color: #6B5559;">
              <span id="arrow-{{ $maSp }}" style="display: inline-block; width: 16px; color: #8B1E2D; font-size: 11px; transition: transform 0.2s ease;">▶</span>
              <strong style="color: #241417; font-family: monospace;">{{ $maSp }}</strong>
            </td>
            <td style="padding: 12px 16px; font-weight: 500; color: #241417;">
              {{ $sp->Ten_san_pham }}
            </td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $sp->Nien_vu ?? '—' }}
            </td>
            <td style="padding: 12px 16px; color: #6B5559;">
              {{ $sp->Dung_tich ? $sp->Dung_tich . ' ml' : '—' }}
            </td>
            <td style="padding: 12px 16px; text-align: right; color: #241417;">
              {{ number_format($tonThucKho) }}
            </td>
            <td style="padding: 12px 16px; text-align: right; font-weight: 600; color: {{ $tonKhaDungKho > 0 ? '#1E6E38' : '#6B5559' }};">
              {{ number_format($tonKhaDungKho) }}
            </td>
            <td style="padding: 12px 16px; text-align: right; color: #241417;">
              {{ number_format($tonDuKienKho) }}
            </td>
            <td style="padding: 12px 16px; text-align: right; color: #6B5559; font-weight: 500;">
              {{ number_format($tonCongTy) }}
            </td>
          </tr>

          {{-- Dòng con hiển thị danh sách Lô (Accordion) --}}
          <tr id="lot-row-{{ $maSp }}" style="display: none; background: #FAF5F3;">
            <td colspan="8" style="padding: 6px 16px 16px 44px;">
              <div style="background: #FFFFFF; border: 1px solid #EADFDC; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="padding: 10px 14px 8px; font-size: 13px; font-weight: 600; color: #5E1320; background: #FAF5F3; border-bottom: 1px solid #EADFDC;">
                  Tồn theo lô tại {{ $tenKho }} · xếp theo thứ tự xuất FIFO
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                  <thead>
                    <tr style="background: #F6ECEA; text-align: left;">
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320; width: 100px;">Thứ tự xuất</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320;">Số lô</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320;">Ngày nhập đầu tiên</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320;">Ngày sản xuất</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320;">Hạn sử dụng</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 110px;">Tồn thực</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 110px;">Đang giữ</th>
                      <th scope="col" style="padding: 8px 12px; font-weight: 600; color: #5E1320; text-align: right; width: 110px;">Khả dụng</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($lots as $lot)
                      <tr style="border-top: 1px solid #F0E6E3;">
                        <td style="padding: 8px 12px; color: #6B5559;">{{ $lot['thu_tu_xuat'] }}</td>
                        <td style="padding: 8px 12px; font-weight: 600; color: #241417;">
                          {{ $lot['so_lo'] }}
                          <span style="font-size: 11px; color: #8C7075; font-weight: normal; margin-left: 4px;">({{ $lot['ma_lo'] }})</span>
                        </td>
                        <td style="padding: 8px 12px; color: #241417;">
                          {{ $lot['ngay_nhap_dau_tien'] ? \Carbon\Carbon::parse($lot['ngay_nhap_dau_tien'])->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding: 8px 12px; color: #6B5559;">
                          {{ $lot['ngay_san_xuat'] ? \Carbon\Carbon::parse($lot['ngay_san_xuat'])->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding: 8px 12px; color: #6B5559;">
                          {{ $lot['han_su_dung'] ? \Carbon\Carbon::parse($lot['han_su_dung'])->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding: 8px 12px; text-align: right; color: #241417;">
                          {{ number_format($lot['ton_thuc']) }}
                        </td>
                        <td style="padding: 8px 12px; text-align: right; color: #6B5559;">
                          {{ number_format($lot['dang_giu']) }}
                        </td>
                        <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: {{ $lot['kha_dung'] > 0 ? '#1E6E38' : '#6B5559' }};">
                          {{ number_format($lot['kha_dung']) }}
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="8" style="padding: 14px 16px; text-align: center; color: #8C7075; font-style: italic;">
                          Không có lô nào có tồn thực tại kho này.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding: 32px 16px; text-align: center; color: #8C7075;">
              Không tìm thấy sản phẩm nào phù hợp với bộ lọc.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <span style="font-size: 13px; color: #6B5559;">
    Đơn vị: chai. Bấm vào một sản phẩm để mở tồn theo từng lô; bấm lần nữa để đóng.
  </span>
</div>

<script>
  function isRowOpen(maSp) {
    const lotRow = document.getElementById('lot-row-' + maSp);
    return lotRow && lotRow.style.display !== 'none';
  }

  function toggleLotRow(maSp) {
    const lotRow = document.getElementById('lot-row-' + maSp);
    const parentRow = document.getElementById('parent-row-' + maSp);
    const arrow = document.getElementById('arrow-' + maSp);
    if (!lotRow) return;

    if (lotRow.style.display === 'none' || lotRow.style.display === '') {
      lotRow.style.display = 'table-row';
      parentRow.style.background = '#FAF2F0';
      if (arrow) {
        arrow.textContent = '▼';
        arrow.style.transform = 'translateY(1px)';
      }
    } else {
      lotRow.style.display = 'none';
      parentRow.style.background = 'transparent';
      if (arrow) {
        arrow.textContent = '▶';
        arrow.style.transform = 'none';
      }
    }
  }
</script>
@endsection

