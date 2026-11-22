<?php

namespace App\Http\Controllers\QuanTri;

use App\Http\Controllers\Controller;
use App\Models\Kho;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KhoController extends Controller
{
    /**
     * ADM-05: Quản lý kho (GET /quan-tri/kho)
     */
    public function index()
    {
        $khos = Kho::withCount('nguoiDung')->get();
        return view('quantri.kho.index', compact('khos'));
    }

    /**
     * Thêm kho mới (POST /quan-tri/kho)
     */
    public function store(Request $request)
    {
        $request->validate([
            'Ma_kho' => 'required|string|max:10|unique:Kho,Ma_kho|regex:/^[A-Z0-9]+$/',
            'Ten_kho' => 'required|string|max:100|unique:Kho,Ten_kho',
            'Dia_chi' => 'required|string|max:255',
            'Trang_thai' => 'required|in:Hoạt động,Ngừng',
        ], [
            'Ma_kho.required' => 'Vui lòng nhập mã kho.',
            'Ma_kho.unique' => 'Mã kho này đã tồn tại.',
            'Ma_kho.regex' => 'Mã kho viết tắt in hoa, không dấu (ví dụ: HN, HCM).',
            'Ten_kho.required' => 'Vui lòng nhập tên kho.',
            'Ten_kho.unique' => 'Tên kho này đã tồn tại.',
            'Dia_chi.required' => 'Vui lòng nhập địa chỉ kho.',
        ]);

        $kho = new Kho();
        $kho->Ma_kho = strtoupper($request->Ma_kho);
        $kho->Ten_kho = $request->Ten_kho;
        $kho->Dia_chi = $request->Dia_chi;
        $kho->Trang_thai = $request->Trang_thai;
        $kho->save();

        ActivityLogger::log('Tạo', 'Kho', "Tạo mới kho {$kho->Ten_kho} ({$kho->Ma_kho})", $kho);

        return redirect()->route('quantri.kho.index')->with('success', "Đã lưu kho {$kho->Ma_kho}.");
    }

    /**
     * Cập nhật thông tin kho (POST /quan-tri/kho/{ma})
     */
    public function update(Request $request, $ma)
    {
        $kho = Kho::where('Ma_kho', $ma)->firstOrFail();

        $request->validate([
            'Ten_kho' => "required|string|max:100|unique:Kho,Ten_kho,{$ma},Ma_kho",
            'Dia_chi' => 'required|string|max:255',
            'Trang_thai' => 'required|in:Hoạt động,Ngừng',
        ], [
            'Ten_kho.required' => 'Vui lòng nhập tên kho.',
            'Ten_kho.unique' => 'Tên kho này đã tồn tại.',
            'Dia_chi.required' => 'Vui lòng nhập địa chỉ kho.',
        ]);

        $changes = [];
        if ($kho->Ten_kho !== $request->Ten_kho) $changes[] = "Tên: {$kho->Ten_kho} -> {$request->Ten_kho}";
        if ($kho->Dia_chi !== $request->Dia_chi) $changes[] = "Địa chỉ: {$kho->Dia_chi} -> {$request->Dia_chi}";
        if ($kho->Trang_thai !== $request->Trang_thai) $changes[] = "Trạng thái: {$kho->Trang_thai} -> {$request->Trang_thai}";

        $kho->Ten_kho = $request->Ten_kho;
        $kho->Dia_chi = $request->Dia_chi;
        $kho->Trang_thai = $request->Trang_thai;
        $kho->save();

        ActivityLogger::log('Sửa', 'Kho', "Cập nhật thông tin kho {$kho->Ma_kho} (" . implode(', ', $changes) . ")", $kho);

        return redirect()->route('quantri.kho.index')->with('success', "Đã lưu kho {$kho->Ma_kho}.");
    }
}

