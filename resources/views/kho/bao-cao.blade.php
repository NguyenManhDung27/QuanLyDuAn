@extends('layouts.app')

@section('title', 'Báo cáo – Soul Wine')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

  {{-- Header & Xuất file --}}
  <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px;">
    <div style="display: flex; flex-direction: column; gap: 4px;">
      <span style="font-size: 13px; color: #6B5559;">Báo cáo / <strong style="color: #241417;">{{ $data['name'] }}</strong></span>
      <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #241417;">Báo cáo</h1>
      <p style="margin: 0; font-size: 14px; color: #6B5559;">Số liệu của {{ $kho->Ten_kho ?? 'Kho' }}, tính theo số lượng chai.</p>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
      <button type="button" onclick="window.print()" title="In hoặc lưu thành PDF bằng chức năng in của trình duyệt" 
              style="height: 42px; padding: 0 16px; border-radius: 8px; border: 1px solid #8B1E2D; background: #FFFFFF; color: #8B1E2D; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
        In / Lưu PDF
      </button>

      <a href="{{ route('kho.bao-cao.export', ['tab' => $tab, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'q' => $q]) }}" 
         style="display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3v12"></path>
          <path d="M7 10l5 5 5-5"></path>
          <path d="M5 21h14"></path>
        </svg>
        Xuất Excel
      </a>
    </div>
  </div>

  {{-- Tab selector --}}
  <div role="tablist" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @php
      $tabs = [
        0 => 'Nhập – xuất – tồn',
        1 => 'Giao hàng theo nhà cung cấp',
        2 => 'Hàng hỏng',
        3 => 'Chênh lệch kiểm kê',
      ];
    @endphp
    @foreach($tabs as $idx => $label)
      <a href="{{ route('kho.bao-cao.index', ['tab' => $idx, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'q' => $q]) }}"
         role="tab"
         style="height: 40px; padding: 0 16px; border-radius: 999px; font: inherit; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; cursor: pointer; transition: all 0.15s ease;
                {{ $tab === $idx ? 'border: 0; background: #6E1423; color: #FFFFFF; font-weight: 600;' : 'border: 1px solid #DCCAC6; background: #FFFFFF; color: #241417;' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  <p style="margin: 0; font-size: 14px; color: #3B2A2D;">{{ $data['desc'] }}</p>

  {{-- Bộ lọc ngày & tìm kiếm --}}
  <form method="GET" action="{{ route('kho.bao-cao.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; padding: 18px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC;">
    <input type="hidden" name="tab" value="{{ $tab }}">

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 150px; font-size: 13px; font-weight: 500;">
      Từ ngày
      <input type="date" name="tu_ngay" value="{{ $tuNgay }}" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 150px; font-size: 13px; font-weight: 500;">
      Đến ngày
      <input type="date" name="den_ngay" value="{{ $denNgay }}" style="height: 42px; padding: 0 10px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">
    </label>

    <label style="display: flex; flex-direction: column; gap: 6px; flex: 2 1 220px; font-size: 13px; font-weight: 500;">
      {{ $data['searchLabel'] }}
      <input type="search" name="q" value="{{ $q }}" placeholder="{{ $data['searchPh'] }}" style="height: 42px; padding: 0 12px; border-radius: 8px; border: 1px solid #DCCAC6; font: inherit; font-size: 14px; background: #FFFFFF;">
    </label>

    <button type="submit" style="height: 42px; padding: 0 20px; border-radius: 8px; border: 0; background: #8B1E2D; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
      Xem báo cáo
    </button>
  </form>

  {{-- Bảng báo cáo --}}
  <div style="border-radius: 12px; background: #FFFFFF; border: 1px solid #EADFDC; overflow-x: auto;">
    <table style="width: 100%; min-width: 960px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #F6ECEA;">
          @foreach($data['cols'] as $c)
            <th scope="col" style="padding: 12px 16px; font-weight: 600; color: #5E1320; white-space: nowrap; text-align: {{ $c['align'] }};">
              {{ $c['label'] }}
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @if(empty($data['rows']))
          <tr>
            <td colspan="{{ count($data['cols']) }}" style="padding: 32px; text-align: center; color: #6B5559;">
              Không có dữ liệu trong kỳ này.
            </td>
          </tr>
        @else
          @foreach($data['rows'] as $row)
            <tr style="border-top: 1px solid #F0E6E3;">
              @foreach($row['cells'] as $cell)
                <td style="padding: 12px 16px; text-align: {{ $cell['align'] }}; {{ isset($cell['color']) ? 'font-weight: 600; color: ' . $cell['color'] . ';' : '' }}">
                  {{ $cell['v'] }}
                </td>
              @endforeach
            </tr>
          @endforeach
        @endif
      </tbody>
      @if(!empty($data['rows']) && !empty($data['foot']))
        <tfoot>
          <tr style="border-top: 2px solid #EADFDC; background: #FCF8F7; font-weight: 700;">
            @foreach($data['foot'] as $f)
              <td style="padding: 12px 16px; text-align: {{ $f['align'] }}; {{ isset($f['color']) ? 'color: ' . $f['color'] . ';' : '' }}">
                {{ $f['v'] }}
              </td>
            @endforeach
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <span style="font-size: 13px; color: #6B5559;">{{ $data['note'] }}</span>

</div>
@endsection

