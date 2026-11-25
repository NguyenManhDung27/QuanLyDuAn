<?php

namespace Database\Seeders;

use App\Models\ChiTietDonBan;
use App\Models\ChiTietDonDatHang;
use App\Models\ChiTietKiemKe;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonDatHang;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Lo;
use App\Models\NhaCungCap;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Models\TonKho;
use Illuminate\Database\Seeder;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. NHÀ CUNG CẤP THỰC TẾ (Từ các nhà vang trong bảng giá Chile, Ý, Pháp)
        // =====================================================================
        $nhaCungCaps = [
            ['Ma_ncc' => 'NCC004', 'Ten_ncc' => 'Bodegas Tagua Tagua', 'Quoc_gia' => 'Chile', 'Dia_chi' => 'Valle de Rapel, San Vicente de Tagua Tagua, Chile', 'Ma_so_thue' => 'CL76100234', 'Nguoi_lien_he' => 'Mateo Silva', 'So_dien_thoai' => '+56 2 2481 5500', 'Email' => 'export@taguatagua.cl', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC005', 'Ten_ncc' => "Fattoria Conca d'Oro", 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via Conegliano 12, 31020 Treviso, Italy', 'Ma_so_thue' => 'IT03491820', 'Nguoi_lien_he' => 'Luca Bellini', 'So_dien_thoai' => '+39 0438 980123', 'Email' => 'sales@concadoro.it', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC006', 'Ten_ncc' => 'Querciabella', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via Santa Lucia 20, 50022 Greve in Chianti, Italy', 'Ma_so_thue' => 'IT04512980', 'Nguoi_lien_he' => 'Giulia Conti', 'So_dien_thoai' => '+39 055 853307', 'Email' => 'info@querciabella.com', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC007', 'Ten_ncc' => 'Aldegheri', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via A. De Gasperi 31, 37015 Sant Ambrogio di Valpolicella, Italy', 'Ma_so_thue' => 'IT02849103', 'Nguoi_lien_he' => 'Marco Aldegheri', 'So_dien_thoai' => '+39 045 6861356', 'Email' => 'export@cantinaaldegheri.it', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC008', 'Ten_ncc' => 'Cantine Farina', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via Alberto Bolla 11, 37029 San Pietro in Cariano, Italy', 'Ma_so_thue' => 'IT01928374', 'Nguoi_lien_he' => 'Elena Farina', 'So_dien_thoai' => '+39 045 7701204', 'Email' => 'info@farinawines.com', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC009', 'Ten_ncc' => 'Feudi di Guagnano', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via Provinciale Veglie 14, 73010 Guagnano, Lecce, Italy', 'Ma_so_thue' => 'IT03829104', 'Nguoi_lien_he' => 'Gianluca Bruno', 'So_dien_thoai' => '+39 0832 705422', 'Email' => 'commerciale@feudidiguagnano.it', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC010', 'Ten_ncc' => 'Cantina Vecchia Torre', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Via Isonzo 25, 73045 Leverano, Lecce, Italy', 'Ma_so_thue' => 'IT00981234', 'Nguoi_lien_he' => 'Antonio Greco', 'So_dien_thoai' => '+39 0832 925053', 'Email' => 'info@vecchiatorre.it', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC011', 'Ten_ncc' => 'Tenute Chiaromonte', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Contrada Gaudiano, 70021 Acquaviva delle Fonti, Italy', 'Ma_so_thue' => 'IT05612093', 'Nguoi_lien_he' => 'Nicola Chiaromonte', 'So_dien_thoai' => '+39 080 762140', 'Email' => 'export@tenutechiaromonte.com', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC012', 'Ten_ncc' => 'Masseria Tagaro', 'Quoc_gia' => 'Ý', 'Dia_chi' => 'Contrada Tagaro 38, 70010 Locorotondo, Italy', 'Ma_so_thue' => 'IT06723109', 'Nguoi_lien_he' => 'Michele Lorusso', 'So_dien_thoai' => '+39 080 4312299', 'Email' => 'sales@tagaro.it', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC013', 'Ten_ncc' => 'Domaine Saint André', 'Quoc_gia' => 'Pháp', 'Dia_chi' => 'Route de Pomerols, 34140 Mèze, France', 'Ma_so_thue' => 'FR48291038', 'Nguoi_lien_he' => 'Pierre Moreau', 'So_dien_thoai' => '+33 4 67 43 82 10', 'Email' => 'contact@domainesaintandre.fr', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC014', 'Ten_ncc' => 'Château Puybarbe', 'Quoc_gia' => 'Pháp', 'Dia_chi' => '1 Puybarbe, 33710 Mombrier, Bourg, France', 'Ma_so_thue' => 'FR82910345', 'Nguoi_lien_he' => 'Claire Laurent', 'So_dien_thoai' => '+33 5 57 68 41 20', 'Email' => 'chateau@puybarbe.fr', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC015', 'Ten_ncc' => 'Xavier Vignon', 'Quoc_gia' => 'Pháp', 'Dia_chi' => '18 Route de Courthézon, 84230 Châteauneuf-du-Pape, France', 'Ma_so_thue' => 'FR71920384', 'Nguoi_lien_he' => 'Xavier Vignon', 'So_dien_thoai' => '+33 4 90 83 55 12', 'Email' => 'export@xaviervignon.com', 'Trang_thai' => 'Đang giao dịch'],
            ['Ma_ncc' => 'NCC016', 'Ten_ncc' => 'Château La Coste', 'Quoc_gia' => 'Pháp', 'Dia_chi' => '2750 Route de la Cride, 13610 Le Puy-Sainte-Réparade, France', 'Ma_so_thue' => 'FR65019283', 'Nguoi_lien_he' => 'Sophie Bernard', 'So_dien_thoai' => '+33 4 42 61 92 92', 'Email' => 'wine@chateau-la-coste.com', 'Trang_thai' => 'Đang giao dịch'],
        ];

        foreach ($nhaCungCaps as $ncc) {
            NhaCungCap::updateOrCreate(['Ma_ncc' => $ncc['Ma_ncc']], $ncc);
        }

        // =====================================================================
        // 2. 70 SẢN PHẨM RƯỢU VANG THỰC TẾ (SP0006 -> SP0075)
        // =====================================================================
        // Format: [Ma_san_pham, Ten_san_pham, Nien_vu, Loai_vang, Quoc_gia, Vung, Nha_san_xuat, Dung_tich, Nong_do_con, Quy_cach_thung, Gia_niem_yet, Trang_thai]
        $rawWines = [
            // I. CHILE - BODEGAS TAGUA TAGUA (15 chai)
            ['SP0006', 'Impido Carmenere', 2023, 'Đỏ', 'Chile', 'Central Valley', 'Bodegas Tagua Tagua', 750, 13.0, 6, 210000, 'Đang kinh doanh'],
            ['SP0007', 'Mandala Cabernet Sauvignon', 2023, 'Đỏ', 'Chile', 'Central Valley', 'Bodegas Tagua Tagua', 750, 13.0, 6, 210000, 'Đang kinh doanh'],
            ['SP0008', 'Fortunatto Sauvignon Blanc', 2024, 'Trắng', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 12.5, 6, 255000, 'Đang kinh doanh'],
            ['SP0009', 'Fortunatto Carmenere (White Label)', 2024, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 255000, 'Đang kinh doanh'],
            ['SP0010', 'Fortunatto Carmenere (Red Label)', 2024, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 255000, 'Đang kinh doanh'],
            ['SP0011', 'Equinoccio Carmenere', 2023, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 255000, 'Đang kinh doanh'],
            ['SP0012', 'Fortunatto Reserva Carmenere', 2024, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 368000, 'Đang kinh doanh'],
            ['SP0013', 'Fortunatto Gran Reserva Carmenere', 2022, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 14.0, 6, 468000, 'Đang kinh doanh'],
            ['SP0014', 'Fortunatto Family Reserve Carmenere', 2022, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 14.5, 6, 757000, 'Đang kinh doanh'],
            ['SP0015', 'Mosqueteros Gran Reserva Chardonnay', 2024, 'Trắng', 'Chile', 'Casablanca Valley', 'Bodegas Tagua Tagua', 750, 13.0, 6, 535000, 'Đang kinh doanh'],
            ['SP0016', 'Mosqueteros Superior', 2024, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 285000, 'Đang kinh doanh'],
            ['SP0017', 'Mosqueteros Reserva Cabernet Sauvignon', 2024, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 13.5, 6, 405000, 'Đang kinh doanh'],
            ['SP0018', 'Mosqueteros Gran Reserva Cabernet Sauvignon', 2023, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 14.0, 6, 535000, 'Đang kinh doanh'],
            ['SP0019', 'Mosqueteros Family Reserve Cabernet Sauvignon', 2023, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 14.5, 6, 850000, 'Đang kinh doanh'],
            ['SP0020', 'Mosqueteros Icono Cabernet Sauvignon', 2020, 'Đỏ', 'Chile', 'Rapel Valley', 'Bodegas Tagua Tagua', 750, 14.5, 6, 1565000, 'Đang kinh doanh'],

            // II. Ý - FATTORIA CONCA D'ORO, QUERCIABELLA, ALDEGHERI, FARINA, FEUDI DI GUAGNANO, VECCHIA TORRE, CHIAROMONTE, TAGARO (28 chai)
            ['SP0021', "Spumante Prosecco Cuvee Oro Extra Dry DOC Treviso Millesimato", 2023, 'Vang nổ', 'Ý', 'Veneto', "Fattoria Conca d'Oro", 750, 11.0, 6, 702000, 'Đang kinh doanh'],
            ['SP0022', "Spumante Moscato Dolce Cuvee Moskino", null, 'Vang nổ', 'Ý', 'Veneto', "Fattoria Conca d'Oro", 750, 7.0, 6, 812000, 'Đang kinh doanh'],
            ['SP0023', "Spumante Dolce Veleno Demi Sec", null, 'Vang nổ', 'Ý', 'Veneto', "Fattoria Conca d'Oro", 750, 11.0, 6, 827000, 'Đang kinh doanh'],
            ['SP0024', "Prosecco DOC Rosé Brut Millesimato", 2023, 'Vang nổ', 'Ý', 'Veneto', "Fattoria Conca d'Oro", 750, 11.5, 6, 862000, 'Đang kinh doanh'],
            ['SP0025', 'Mongrana Maremma Toscana', 2022, 'Đỏ', 'Ý', 'Tuscany', 'Querciabella', 750, 13.5, 12, 977000, 'Đang kinh doanh'],
            ['SP0026', 'Querciabella Chianti Classico DOCG', 2020, 'Đỏ', 'Ý', 'Tuscany', 'Querciabella', 750, 14.0, 6, 1247000, 'Đang kinh doanh'],
            ['SP0027', 'Turpino Toscana', 2017, 'Đỏ', 'Ý', 'Tuscany', 'Querciabella', 750, 14.5, 6, 1980000, 'Đang kinh doanh'],
            ['SP0028', 'Querciabella Chianti Classico Riserva DOCG', 2019, 'Đỏ', 'Ý', 'Tuscany', 'Querciabella', 750, 14.5, 6, 2084000, 'Đang kinh doanh'],
            ['SP0029', 'Pietra Dolce Passito Veneto IGT', 2018, 'Trắng', 'Ý', 'Veneto', 'Aldegheri', 750, 13.5, 6, 1500000, 'Đang kinh doanh'],
            ['SP0030', 'Sengiarossa Verona Rosso IGT', 2019, 'Đỏ', 'Ý', 'Veneto', 'Aldegheri', 750, 14.0, 6, 1516000, 'Đang kinh doanh'],
            ['SP0031', 'Pietramara Passito Rosso Veneto IGT', 2016, 'Đỏ', 'Ý', 'Veneto', 'Aldegheri', 750, 15.0, 6, 1795000, 'Đang kinh doanh'],
            ['SP0032', 'Amarone Valpolicella Classico DOCG', 2019, 'Đỏ', 'Ý', 'Veneto', 'Aldegheri', 750, 15.5, 6, 3240000, 'Đang kinh doanh'],
            ['SP0033', 'Amarone Valpolicella Classico DOCG Santambrogio', 2019, 'Đỏ', 'Ý', 'Veneto', 'Aldegheri', 750, 16.0, 6, 3265000, 'Đang kinh doanh'],
            ['SP0034', 'Farina Soave Classico DOC', 2023, 'Trắng', 'Ý', 'Veneto', 'Cantine Farina', 750, 12.5, 6, 712000, 'Đang kinh doanh'],
            ['SP0035', 'Farina Valpolicella DOC', 2023, 'Đỏ', 'Ý', 'Veneto', 'Cantine Farina', 750, 12.5, 6, 905000, 'Đang kinh doanh'],
            ['SP0036', 'Valpolicella Classico Superiore DOC', 2022, 'Đỏ', 'Ý', 'Veneto', 'Cantine Farina', 750, 13.5, 6, 998000, 'Đang kinh doanh'],
            ['SP0037', 'Valpolicella Ripasso DOC Classico Superiore Montecorna', 2021, 'Đỏ', 'Ý', 'Veneto', 'Cantine Farina', 750, 14.0, 6, 1356000, 'Đang kinh doanh'],
            ['SP0038', 'Terramare Negroamaro IGT Salento', 2021, 'Đỏ', 'Ý', 'Puglia', 'Feudi di Guagnano', 750, 13.5, 6, 755000, 'Đang kinh doanh'],
            ['SP0039', 'Terramare Primitivo IGT Salento', 2021, 'Đỏ', 'Ý', 'Puglia', 'Feudi di Guagnano', 750, 14.0, 6, 850000, 'Đang kinh doanh'],
            ['SP0040', 'Le Camarde Negroamaro & Primitivo IGT Salento', 2021, 'Đỏ', 'Ý', 'Puglia', 'Feudi di Guagnano', 750, 14.5, 6, 1080000, 'Đang kinh doanh'],
            ['SP0041', 'Vermentino IGP Salento Bianco', 2023, 'Trắng', 'Ý', 'Puglia', 'Cantina Vecchia Torre', 750, 12.5, 6, 540000, 'Đang kinh doanh'],
            ['SP0042', 'DOP Leverano Rosso', 2020, 'Đỏ', 'Ý', 'Puglia', 'Cantina Vecchia Torre', 750, 13.0, 6, 435000, 'Đang kinh doanh'],
            ['SP0043', 'Salice Salentino DOP Rosso', 2019, 'Đỏ', 'Ý', 'Puglia', 'Cantina Vecchia Torre', 750, 13.5, 6, 595000, 'Đang kinh doanh'],
            ['SP0044', 'Auro Primitivo Di Manduria DOP', 2018, 'Đỏ', 'Ý', 'Puglia', 'Cantina Vecchia Torre', 750, 14.5, 6, 830000, 'Đang kinh doanh'],
            ['SP0045', '50° Anniversario IGP Salento Rosso', 2021, 'Đỏ', 'Ý', 'Puglia', 'Cantina Vecchia Torre', 750, 14.5, 6, 1110000, 'Đang kinh doanh'],
            ['SP0046', 'Puglia IGT Primitivo Mascherone Bio', 2022, 'Đỏ', 'Ý', 'Puglia', 'Tenute Chiaromonte', 750, 14.5, 6, 1085000, 'Đang kinh doanh'],
            ['SP0047', 'DOC Gioia Del Colle Muro Sant Angelo', 2021, 'Đỏ', 'Ý', 'Puglia', 'Tenute Chiaromonte', 750, 16.0, 6, 1755000, 'Đang kinh doanh'],
            ['SP0048', 'Fuga Primitivo IGT Puglia', 2022, 'Đỏ', 'Ý', 'Puglia', 'Masseria Tagaro', 750, 14.0, 6, 580000, 'Đang kinh doanh'],

            // III. PHÁP - SAINT ANDRE, PUYBARBE, XAVIER VIGNON, LA COSTE (27 chai)
            ['SP0049', "Domaine Saint-André Terroir Maritime IGP Pays d'Oc Blanc", 2024, 'Trắng', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 12.5, 6, 467000, 'Đang kinh doanh'],
            ['SP0050', 'Domaine Saint-André Parcellaire Chardonnay Blanc', 2024, 'Trắng', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 13.0, 6, 585000, 'Đang kinh doanh'],
            ['SP0051', 'Domaine Saint-André Parcellaire Sauvignon Blanc', 2024, 'Trắng', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 12.5, 6, 585000, 'Đang kinh doanh'],
            ['SP0052', 'Pas Des Arches Rouge Blend Vin de France', 2024, 'Đỏ', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 13.0, 6, 289000, 'Đang kinh doanh'],
            ['SP0053', 'La Croisade Réserve Cabernet - Syrah Rouge', 2024, 'Đỏ', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 13.5, 6, 389000, 'Đang kinh doanh'],
            ['SP0054', "Domaine Saint-André Folie d'Ines Rouge", 2022, 'Đỏ', 'Pháp', 'Languedoc', 'Domaine Saint André', 750, 14.0, 6, 710000, 'Đang kinh doanh'],
            ['SP0055', "L'Etoile de Château Puybarbe", 2020, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 750, 14.0, 6, 520000, 'Đang kinh doanh'],
            ['SP0056', 'Gaia de Château Puybarbe', 2020, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 750, 14.5, 6, 652000, 'Đang kinh doanh'],
            ['SP0057', 'Astral de Château Puybarbe', 2020, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 750, 14.5, 6, 737000, 'Đang kinh doanh'],
            ['SP0058', 'Le Roc de Château Puybarbe', 2020, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 750, 14.5, 6, 822000, 'Đang kinh doanh'],
            ['SP0059', 'Magnum Le Roc de Château Puybarbe', 2019, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 1500, 14.5, 6, 1958000, 'Đang kinh doanh'],
            ['SP0060', 'Château Puybarbe', 2019, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 750, 14.5, 6, 1168000, 'Đang kinh doanh'],
            ['SP0061', 'Magnum Château Puybarbe', 2017, 'Đỏ', 'Pháp', 'Bordeaux', 'Château Puybarbe', 1500, 14.5, 6, 2647000, 'Đang kinh doanh'],
            ['SP0062', 'Xavier Vignon Côtes du Rhône Blanc', 2020, 'Trắng', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 13.5, 6, 715000, 'Đang kinh doanh'],
            ['SP0063', 'Xavier Vignon Châteauneuf du Pape Blanc', 2023, 'Trắng', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.0, 6, 2495000, 'Đang kinh doanh'],
            ['SP0064', 'Xavier Vignon Côtes du Rhône Vintage Air Ball Rouge', 2020, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.0, 6, 510000, 'Đang kinh doanh'],
            ['SP0065', 'Xavier Vignon Ventoux Rouge', 2022, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.0, 6, 657000, 'Đang kinh doanh'],
            ['SP0066', 'Magnum Xavier Vignon Ventoux Rouge', 2021, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 1500, 14.0, 6, 1552000, 'Đang kinh doanh'],
            ['SP0067', 'Xavier Vignon Vacqueyras Rouge Bio', 2022, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.5, 6, 1377000, 'Đang kinh doanh'],
            ['SP0068', 'Xavier Vignon Gigondas Rouge', 2019, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.5, 6, 1697000, 'Đang kinh doanh'],
            ['SP0069', 'Xavier Vignon Châteauneuf du Pape Rouge', 2021, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 15.0, 6, 2374000, 'Đang kinh doanh'],
            ['SP0070', 'Arcane - La Tempérance Rouge Bio', null, 'Đỏ', 'Pháp', 'Rhône Valley', 'Xavier Vignon', 750, 14.5, 6, 1484000, 'Đang kinh doanh'],
            ['SP0071', 'Lady A Rosé IGP Méditerranée Sélection La Coste', 2023, 'Hồng', 'Pháp', 'Provence', 'Château La Coste', 750, 12.5, 6, 1065000, 'Đang kinh doanh'],
            ['SP0072', "Rosé d'Une Nuit AOP Coteaux d'Aix", 2024, 'Hồng', 'Pháp', 'Provence', 'Château La Coste', 750, 12.5, 6, 1300000, 'Đang kinh doanh'],
            ['SP0073', 'Nooh Sparkling Rosé - No Low 0.0%', null, 'Vang nổ', 'Pháp', 'Provence', 'Château La Coste', 750, 0.0, 6, 1700000, 'Đang kinh doanh'],
            // 2 sản phẩm cài sẵn kịch bản đặc biệt: chưa niêm yết giá & ngừng kinh doanh
            ['SP0074', 'Grand Vin Blanc IGP Méditerranée', 2023, 'Trắng', 'Pháp', 'Provence', 'Château La Coste', 750, 13.0, 6, null, 'Đang kinh doanh'],
            ['SP0075', 'Grand Vin Rouge AOP Aix en Provence', 2020, 'Đỏ', 'Pháp', 'Provence', 'Château La Coste', 750, 14.0, 6, 2475000, 'Ngừng'],
        ];

        $giaVonMap = [];
        $giaBanMap = [];

        foreach ($rawWines as $w) {
            [$ma, $ten, $nienVu, $loai, $qg, $vung, $nsx, $dt, $nongDo, $qc, $giaBan, $tt] = $w;
            // Giá vốn bình quân ~ 65% giá bán lẻ (chẵn nghìn)
            $giaVon = $giaBan ? round(($giaBan * 0.65) / 1000) * 1000 : 0.00;
            $giaVonMap[$ma] = $giaVon;
            $giaBanMap[$ma] = $giaBan ?? 0;

            SanPham::updateOrCreate(
                ['Ma_san_pham' => $ma],
                [
                    'Ten_san_pham' => $ten,
                    'Nien_vu' => $nienVu,
                    'Loai_vang' => $loai,
                    'Quoc_gia' => $qg,
                    'Vung' => $vung,
                    'Nha_san_xuat' => $nsx,
                    'Dung_tich' => $dt,
                    'Nong_do_con' => $nongDo,
                    'Don_vi_tinh' => 'Chai',
                    'Quy_cach_thung' => $qc,
                    'Gia_niem_yet' => $giaBan,
                    'Gia_von_binh_quan' => $giaVon,
                    'Trang_thai' => $tt,
                ]
            );
        }

        // =====================================================================
        // 3. 20 KHÁCH HÀNG B2B THỰC TẾ (Từ File 2, giữ SĐT chuẩn hóa 10 số)
        // =====================================================================
        $khachHangs = [
            ['KH0005', 'CTCP Xây dựng & Phát triển Thương mại Hoàng Lâm', 'Doanh nghiệp', '0108112233', '0943388118', 'hoanglam.trade@gmail.com', 'Hà Nội', 'Đang giao dịch'],
            ['KH0006', 'Công ty TNHH Xây dựng Shari Cons', 'Doanh nghiệp', '0108223344', '0965270588', 'sharicons@gmail.com', 'Cầu Giấy, Hà Nội', 'Đang giao dịch'],
            ['KH0007', 'Hộ kinh doanh Delia Beauty Center', 'Doanh nghiệp', '0108334455', '0916881618', 'deliabeauty@gmail.com', 'Tòa A - 122 Xuân Thủy, Cầu Giấy, Hà Nội', 'Đang giao dịch'],
            ['KH0008', 'Trung Tâm Chuyển Giao Spa Dưỡng Sinh', 'Doanh nghiệp', '0108445566', '0914799688', 'spaduongsinh@gmail.com', 'Tòa Sunsquare 21 Lê Đức Thọ, Mỹ Đình, Hà Nội', 'Đang giao dịch'],
            ['KH0009', 'Công ty Kiến Trúc & Xây Dựng ArChicentury VN', 'Doanh nghiệp', '0108556677', '0976440244', 'archicentury@gmail.com', 'Số 16 ngõ 75 Trần Quang Diệu, Đống Đa, Hà Nội', 'Đang giao dịch'],
            ['KH0010', 'Công ty TNHH Tiên Cát Tới', 'Doanh nghiệp', '0108667788', '0972553059', 'tiencattoi@gmail.com', 'Số 38 đường 2.2 KĐT Gamuda Garden, Hoàng Mai, Hà Nội', 'Đang giao dịch'],
            ['KH0011', 'Công ty VNNA & Associates', 'Doanh nghiệp', '0108778899', '0986712699', 'contact@vnna.vn', 'D01 - L08 An Vượng Villa, Dương Nội, Hà Đông, Hà Nội', 'Đang giao dịch'],
            ['KH0012', 'Nhà hàng 703 Beer', 'Doanh nghiệp', '0108889900', '0359360383', '703beer.hn@gmail.com', 'G3 Chung cư Vinhomes Green Bay, Mễ Trì, Hà Nội', 'Đang giao dịch'],
            ['KH0013', 'Công ty TNHH Liên Minh Việt Nam', 'Doanh nghiệp', '0108990011', '0968463488', 'lienminhvn@gmail.com', '9 Hàng Muối, Hoàn Kiếm, Hà Nội', 'Đang giao dịch'],
            ['KH0014', 'Công ty TNHH Thương mại Đầu tư Lucky Cat Việt Nam', 'Doanh nghiệp', '0109001122', '0902250587', 'luckycat.vn@gmail.com', 'Số 27 ngõ 97 Nguyễn Đổng Chi, Nam Từ Liêm, Hà Nội', 'Đang giao dịch'],
            ['KH0015', 'CTCP Công nghệ và Giáo dục MSUN', 'Doanh nghiệp', '0109112233', '0374817577', 'info@msun.edu.vn', 'Sảnh S02 Vinhomes Smart City, Tây Mỗ, Hà Nội', 'Đang giao dịch'],
            ['KH0016', 'Công ty TNHH Yori Hiroko Việt Nam', 'Doanh nghiệp', '0109223344', '0917946345', 'yorihiroko@gmail.com', 'M2 Mipec Kiến Hưng, Hà Đông, Hà Nội', 'Đang giao dịch'],
            ['KH0017', 'Công ty CP Dịch vụ Ăn uống Green Food', 'Doanh nghiệp', '0109334455', '0975033969', 'greenfood.hn@gmail.com', 'Tòa HUD3, 121-123 Tô Hiệu, Hà Đông, Hà Nội', 'Đang giao dịch'],
            ['KH0018', 'Công ty Cổ phần Pizza 4PS', 'Doanh nghiệp', '0312987654', '0908414102', 'anh.bt@pizza4ps.com', 'Quận 1, TP. Hồ Chí Minh', 'Đang giao dịch'],
            ['KH0019', 'Công ty Cổ phần Tập đoàn Vàng bạc Đá quý DOJI', 'Doanh nghiệp', '0100365621', '0989146868', 'anh.ltq@doji.vn', 'Số 5 Lê Duẩn, Ba Đình, Hà Nội', 'Đang giao dịch'],
            ['KH0020', 'Công ty Cổ phần Đầu tư Vua Nệm', 'Doanh nghiệp', '0107123456', '0904909181', 'anh.hoang@vuanem.com', 'Hai Bà Trưng, Hà Nội', 'Đang giao dịch'],
            ['KH0021', 'Công ty Cổ phần Tập đoàn Masan', 'Doanh nghiệp', '0303576603', '02862563862', 'bac@msn.masangroup.com', 'Quận 1, TP. Hồ Chí Minh', 'Đang giao dịch'],
            ['KH0022', 'Công ty Cổ phần Hàng không Vietjet', 'Doanh nghiệp', '0102325399', '0903736772', 'finance@vietjetair.com', 'Quận Tân Bình, TP. Hồ Chí Minh', 'Đang giao dịch'],
            ['KH0023', 'Công ty Cổ phần Dịch vụ Cáp treo Bà Nà', 'Doanh nghiệp', '0400592682', '0905071075', 'anlh@banahills.com.vn', 'Hòa Vang, Đà Nẵng', 'Đang giao dịch'],
            ['KH0024', 'Công ty TNHH Tư vấn Đầu tư Nguyên Bình', 'Doanh nghiệp', '0108797119', '0975728696', '0108797119nguyenbinh@gmail.com', 'Cầu Giấy, Hà Nội', 'Đang giao dịch'],
        ];

        foreach ($khachHangs as $kh) {
            KhachHang::updateOrCreate(
                ['Ma_khach_hang' => $kh[0]],
                [
                    'Ten_khach_hang' => $kh[1],
                    'Loai_khach_hang' => $kh[2],
                    'Ma_so_thue' => $kh[3],
                    'So_dien_thoai' => $kh[4],
                    'Email' => $kh[5],
                    'Dia_chi' => $kh[6],
                    'Trang_thai' => $kh[7],
                ]
            );
        }

        // =====================================================================
        // 4. LÔ HÀNG & TỒN KHO CHO DANH MỤC VANG MỚI (Có lô sắp hết hạn để test FEFO)
        // =====================================================================
        $lotsConfig = [
            // 3 lô SẮP HẾT HẠN (trong 25-45 ngày tới) để test cảnh báo HSD & FEFO
            ['L2401', 'SP0006', 'LOT-CL-2401', date('Y-m-d', strtotime('-700 days')), date('Y-m-d', strtotime('+28 days')), 'HN', 18],
            ['L2402', 'SP0008', 'LOT-CL-2402', date('Y-m-d', strtotime('-680 days')), date('Y-m-d', strtotime('+42 days')), 'HN', 12],
            ['L2403', 'SP0021', 'LOT-IT-2403', date('Y-m-d', strtotime('-650 days')), date('Y-m-d', strtotime('+35 days')), 'HCM', 12],

            // Lô tiêu chuẩn hạn dài tại HN & HCM
            ['L2404', 'SP0006', 'LOT-CL-2404', '2024-05-10', '2030-05-10', 'HN', 36],
            ['L2405', 'SP0007', 'LOT-CL-2405', '2024-06-15', '2030-06-15', 'HN', 30],
            ['L2406', 'SP0009', 'LOT-CL-2406', '2024-08-01', '2031-08-01', 'HN', 24],
            ['L2407', 'SP0012', 'LOT-CL-2407', '2024-09-12', '2032-09-12', 'HN', 42],
            ['L2408', 'SP0013', 'LOT-CL-2408', '2023-11-20', '2031-11-20', 'HN', 24],
            ['L2409', 'SP0015', 'LOT-CL-2409', '2024-04-18', '2029-04-18', 'HN', 18],
            ['L2410', 'SP0018', 'LOT-CL-2410', '2024-02-10', '2032-02-10', 'HN', 30],
            ['L2411', 'SP0020', 'LOT-CL-2411', '2021-10-05', '2033-10-05', 'HN', 12],
            ['L2412', 'SP0025', 'LOT-IT-2412', '2023-09-14', '2031-09-14', 'HN', 24],
            ['L2413', 'SP0026', 'LOT-IT-2413', '2021-07-22', '2032-07-22', 'HN', 18],
            ['L2414', 'SP0032', 'LOT-IT-2414', '2020-11-11', '2035-11-11', 'HN', 12],
            ['L2415', 'SP0034', 'LOT-IT-2415', '2024-03-08', '2029-03-08', 'HN', 30],
            ['L2416', 'SP0038', 'LOT-IT-2416', '2022-05-19', '2030-05-19', 'HN', 36],
            ['L2417', 'SP0039', 'LOT-IT-2417', '2022-06-25', '2030-06-25', 'HN', 24],
            ['L2418', 'SP0044', 'LOT-IT-2418', '2019-09-30', '2031-09-30', 'HN', 18],
            ['L2419', 'SP0049', 'LOT-FR-2419', '2024-07-10', '2029-07-10', 'HN', 30],
            ['L2420', 'SP0052', 'LOT-FR-2420', '2024-08-15', '2030-08-15', 'HN', 48],
            ['L2421', 'SP0055', 'LOT-FR-2421', '2021-04-12', '2031-04-12', 'HN', 24],
            ['L2422', 'SP0060', 'LOT-FR-2422', '2020-05-20', '2032-05-20', 'HN', 18],
            ['L2423', 'SP0064', 'LOT-FR-2423', '2021-09-01', '2031-09-01', 'HN', 36],
            ['L2424', 'SP0069', 'LOT-FR-2424', '2022-10-18', '2034-10-18', 'HN', 12],
            ['L2425', 'SP0071', 'LOT-FR-2425', '2024-03-22', '2028-03-22', 'HN', 24],

            // Kho HCM
            ['L2426', 'SP0007', 'LOT-CL-2426', '2024-06-20', '2030-06-20', 'HCM', 24],
            ['L2427', 'SP0012', 'LOT-CL-2427', '2024-09-15', '2032-09-15', 'HCM', 30],
            ['L2428', 'SP0021', 'LOT-IT-2428', '2024-05-01', '2029-05-01', 'HCM', 24],
            ['L2429', 'SP0026', 'LOT-IT-2429', '2021-08-10', '2032-08-10', 'HCM', 18],
            ['L2430', 'SP0039', 'LOT-IT-2430', '2022-07-12', '2030-07-12', 'HCM', 36],
            ['L2431', 'SP0052', 'LOT-FR-2431', '2024-08-20', '2030-08-20', 'HCM', 42],
            ['L2432', 'SP0065', 'LOT-FR-2432', '2023-05-14', '2031-05-14', 'HCM', 24],
            ['L2433', 'SP0072', 'LOT-FR-2433', '2024-04-28', '2028-04-28', 'HCM', 18],
        ];

        foreach ($lotsConfig as $item) {
            [$maLo, $maSp, $maLoNcc, $nsx, $hsd, $maKho, $ton] = $item;
            Lo::updateOrCreate(
                ['Ma_lo' => $maLo],
                [
                    'Ma_san_pham' => $maSp,
                    'Ma_lo_ncc' => $maLoNcc,
                    'Ngay_san_xuat' => $nsx,
                    'Han_su_dung' => $hsd,
                ]
            );
            TonKho::updateOrCreate(
                ['Ma_kho' => $maKho, 'Ma_lo' => $maLo],
                [
                    'Ton_thuc' => $ton,
                    'Ngay_nhap_dau_tien' => date('Y-m-d 09:00:00', strtotime('-150 days')),
                ]
            );
        }

        // =====================================================================
        // 5. DỮ LIỆU GIAO DỊCH LỊCH SỬ 6 THÁNG GẦN ĐÂY (Cho Báo cáo & Biểu đồ)
        // =====================================================================
        $historySales = [
            // [Tháng offset, MaDon, MaPhieuXuat, MaKho, MaKH, MaSP, MaLo, SoLuong]
            [-5, 'DB-HIST-0501', 'PX-HIST-0501', 'HN',  'KH0005', 'SP0012', 'L2407', 24],
            [-5, 'DB-HIST-0502', 'PX-HIST-0502', 'HN',  'KH0012', 'SP0026', 'L2413', 12],
            [-4, 'DB-HIST-0401', 'PX-HIST-0401', 'HN',  'KH0013', 'SP0038', 'L2416', 18],
            [-4, 'DB-HIST-0402', 'PX-HIST-0402', 'HCM', 'KH0018', 'SP0021', 'L2428', 24],
            [-3, 'DB-HIST-0301', 'PX-HIST-0301', 'HN',  'KH0019', 'SP0032', 'L2414', 6],
            [-3, 'DB-HIST-0302', 'PX-HIST-0302', 'HN',  'KH0011', 'SP0055', 'L2421', 18],
            [-2, 'DB-HIST-0201', 'PX-HIST-0201', 'HN',  'KH0017', 'SP0006', 'L2404', 36],
            [-2, 'DB-HIST-0202', 'PX-HIST-0202', 'HCM', 'KH0021', 'SP0039', 'L2430', 24],
            [-1, 'DB-HIST-0101', 'PX-HIST-0101', 'HN',  'KH0020', 'SP0064', 'L2423', 24],
            [-1, 'DB-HIST-0102', 'PX-HIST-0102', 'HN',  'KH0007', 'SP0071', 'L2425', 12],
            [0,  'DB-HIST-0001', 'PX-HIST-0001', 'HN',  'KH0012', 'SP0052', 'L2420', 30],
            [0,  'DB-HIST-0002', 'PX-HIST-0002', 'HCM', 'KH0022', 'SP0069', 'L2424', 12],
        ];

        foreach ($historySales as $s) {
            [$monthOffset, $maDb, $maPx, $maKho, $maKh, $maSp, $maLo, $sl] = $s;
            $ngay = date('Y-m-15', strtotime("$monthOffset months"));
            $thoiDiem = $ngay . ' 10:30:00';
            $giaBan = $giaBanMap[$maSp] ?? 500000;
            $giaVon = $giaVonMap[$maSp] ?? 325000;
            $khInfo = KhachHang::find($maKh);

            DonHangBan::updateOrCreate(
                ['Ma_don_hang_ban' => $maDb],
                [
                    'Ma_khach_hang' => $maKh,
                    'Ma_kho' => $maKho,
                    'Ma_nguoi_lap' => 'ND0005',
                    'Thoi_diem_xac_nhan' => $thoiDiem,
                    'Ten_nguoi_nhan' => $khInfo?->Ten_khach_hang ?? 'Khách hàng B2B',
                    'Sdt_nguoi_nhan' => $khInfo?->So_dien_thoai ?? '0901234567',
                    'Dia_chi_giao_hang' => $khInfo?->Dia_chi ?? 'Hà Nội',
                    'Tong_tien_hang' => $sl * $giaBan,
                    'Trang_thai' => 'Hoàn tất',
                ]
            );

            ChiTietDonBan::updateOrCreate(
                ['Ma_don_hang_ban' => $maDb, 'Ma_san_pham' => $maSp],
                [
                    'So_luong' => $sl,
                    'Don_gia_ban' => $giaBan,
                    'Chiet_khau' => 0,
                ]
            );

            PhieuXuatKho::updateOrCreate(
                ['Ma_phieu_xuat' => $maPx],
                [
                    'Ma_don_hang_ban' => $maDb,
                    'Ngay_tao' => $ngay,
                    'Trang_thai_giao_hang' => 'Giao thành công',
                    'Don_vi_van_chuyen' => 'Viettel Post',
                    'Ma_van_don' => 'VTP-' . substr($maPx, -4),
                    'Ma_nguoi_xuat' => 'ND0003',
                    'Thoi_diem_xuat_kho' => $ngay . ' 14:00:00',
                    'Thoi_diem_ket_qua_giao' => date('Y-m-d 16:00:00', strtotime($ngay . ' +1 day')),
                ]
            );

            ChiTietPhieuXuat::updateOrCreate(
                ['Ma_phieu_xuat' => $maPx, 'Ma_lo_phan_bo' => $maLo, 'Ma_lo_thuc_xuat' => $maLo],
                [
                    'So_luong' => $sl,
                    'Gia_von_xuat' => $giaVon,
                ]
            );
        }

        // Lịch sử nhập kho hoàn tất 4 tháng gần đây (khớp báo cáo nhập hàng theo NCC)
        $historyPurchases = [
            [-3, 'DDH-HIST-0301', 'PN-HIST-0301', 'NCC004', 'HN',  'SP0006', 'L2404', 48],
            [-2, 'DDH-HIST-0201', 'PN-HIST-0201', 'NCC006', 'HN',  'SP0026', 'L2413', 24],
            [-1, 'DDH-HIST-0101', 'PN-HIST-0101', 'NCC013', 'HN',  'SP0052', 'L2420', 60],
            [0,  'DDH-HIST-0001', 'PN-HIST-0001', 'NCC015', 'HCM', 'SP0065', 'L2432', 36],
        ];

        foreach ($historyPurchases as $p) {
            [$monthOffset, $maDdh, $maPn, $maNcc, $maKho, $maSp, $maLo, $sl] = $p;
            $ngay = date('Y-m-05', strtotime("$monthOffset months"));
            $giaNhap = $giaVonMap[$maSp] ?? 300000;

            DonDatHang::updateOrCreate(
                ['Ma_don_dat_hang' => $maDdh],
                [
                    'Ma_ncc' => $maNcc,
                    'Ma_kho' => $maKho,
                    'Ma_nguoi_lap' => 'ND0002',
                    'Ngay_lap' => $ngay,
                    'Ngay_du_kien_nhan_du' => date('Y-m-d', strtotime($ngay . ' +5 days')),
                    'Trang_thai' => 'Đã nhận đủ',
                    'Tong_gia_tri' => $sl * $giaNhap,
                    'Ma_nguoi_duyet' => 'ND0004',
                    'Thoi_diem_duyet' => $ngay . ' 11:00:00',
                    'Ma_nguoi_gui_ncc' => 'ND0002',
                    'Ngay_gui_ncc' => $ngay,
                ]
            );

            ChiTietDonDatHang::updateOrCreate(
                ['Ma_don_dat_hang' => $maDdh, 'Ma_san_pham' => $maSp],
                ['So_luong_dat' => $sl, 'Don_gia_mua' => $giaNhap]
            );

            PhieuNhapKho::updateOrCreate(
                ['Ma_phieu_nhap' => $maPn],
                [
                    'Ma_kho' => $maKho,
                    'Loai_nhap' => 'Nhập từ NCC',
                    'Ma_don_dat_hang' => $maDdh,
                    'Ma_nguoi_lap' => 'ND0003',
                    'Ngay_lap' => date('Y-m-d', strtotime($ngay . ' +4 days')),
                    'Thoi_diem_xac_nhan' => date('Y-m-d 15:00:00', strtotime($ngay . ' +4 days')),
                    'So_phieu_giao_ncc' => 'DN-' . substr($maPn, -4),
                    'Dot_giao_cuoi' => true,
                    'Trang_thai' => 'Hoàn tất',
                ]
            );

            ChiTietPhieuNhap::updateOrCreate(
                ['Ma_phieu_nhap' => $maPn, 'Ma_lo' => $maLo],
                [
                    'So_luong_nhap' => $sl,
                    'So_luong_hao_hut' => $maPn === 'PN-HIST-0101' ? 2 : 0, // 2 chai vỡ khi vận chuyển để test báo cáo hàng hỏng
                    'Ly_do_hao_hut' => $maPn === 'PN-HIST-0101' ? 'Vỡ 2 chai do va đập trong lúc dỡ hàng' : null,
                ]
            );
        }

        // 1 Phiếu kiểm kê đã hoàn tất có chênh lệch (để Báo cáo Chênh lệch kiểm kê có dữ liệu)
        PhieuKiemKe::updateOrCreate(
            ['Ma_phieu_kiem_ke' => 'KK-HIST-0001'],
            [
                'Ma_kho' => 'HN',
                'Ma_nguoi_tao' => 'ND0002',
                'Ma_nguoi_kiem' => 'ND0003',
                'Thoi_diem_tao' => date('Y-m-d 08:30:00', strtotime('-20 days')),
                'Thoi_diem_dem_xong' => date('Y-m-d 11:30:00', strtotime('-20 days')),
                'Trang_thai' => 'Hoàn tất',
                'Ma_nguoi_duyet' => 'ND0002',
                'Thoi_diem_duyet' => date('Y-m-d 14:00:00', strtotime('-19 days')),
                'Ghi_chu' => 'Kiểm kê định kỳ quý - Khu vực vang Pháp',
            ]
        );
        ChiTietKiemKe::updateOrCreate(
            ['Ma_phieu_kiem_ke' => 'KK-HIST-0001', 'Ma_lo' => 'L2421'],
            [
                'So_luong_he_thong' => 25,
                'So_luong_thuc_te' => 24,
                'Ly_do_chenh_lech' => 'Hao hụt 1 chai do nứt nút bần khi bảo quản',
                'Gia_von_dieu_chinh' => $giaVonMap['SP0055'] ?? 338000,
            ]
        );

        // =====================================================================
        // 6. CÁC CHỨNG TỪ MỞ ĐỂ DEMO TRỰC TIẾP MỌI KỊCH BẢN TRÊN WEB
        // =====================================================================
        // 6.1. Đề xuất bị TỪ CHỐI (QL Kho vào sửa hoặc gửi duyệt lại)
        DonDatHang::updateOrCreate(
            ['Ma_don_dat_hang' => 'DDH-2026-0003'],
            [
                'Ma_ncc' => 'NCC007',
                'Ma_kho' => 'HN',
                'Ma_nguoi_lap' => 'ND0002',
                'Ngay_lap' => date('Y-m-d', strtotime('-2 days')),
                'Ngay_du_kien_nhan_du' => date('Y-m-d', strtotime('+14 days')),
                'Trang_thai' => 'Từ chối',
                'Tong_gia_tri' => 50544000,
                'Ma_nguoi_duyet' => 'ND0004',
                'Thoi_diem_duyet' => date('Y-m-d 15:00:00', strtotime('-1 day')),
                'Ly_do_tu_choi' => 'Số lượng đặt Amarone đợt này vượt ngân sách tháng, đề nghị giảm xuống 12 chai.',
            ]
        );
        ChiTietDonDatHang::updateOrCreate(
            ['Ma_don_dat_hang' => 'DDH-2026-0003', 'Ma_san_pham' => 'SP0032'],
            ['So_luong_dat' => 24, 'Don_gia_mua' => 2106000]
        );

        // 6.2. Đơn đã gửi NCC nhưng QUÁ HẠN dự kiến nhận đủ (QL Kho xem xét / đóng thiếu; NV Kho nhận hàng)
        DonDatHang::updateOrCreate(
            ['Ma_don_dat_hang' => 'DDH-2026-0004'],
            [
                'Ma_ncc' => 'NCC014',
                'Ma_kho' => 'HN',
                'Ma_nguoi_lap' => 'ND0002',
                'Ngay_lap' => date('Y-m-d', strtotime('-12 days')),
                'Ngay_du_kien_nhan_du' => date('Y-m-d', strtotime('-3 days')),
                'Trang_thai' => 'Đã gửi NCC',
                'Tong_gia_tri' => 12816000,
                'Ma_nguoi_duyet' => 'ND0004',
                'Thoi_diem_duyet' => date('Y-m-d 10:00:00', strtotime('-11 days')),
                'Ma_nguoi_gui_ncc' => 'ND0002',
                'Ngay_gui_ncc' => date('Y-m-d', strtotime('-10 days')),
            ]
        );
        ChiTietDonDatHang::updateOrCreate(
            ['Ma_don_dat_hang' => 'DDH-2026-0004', 'Ma_san_pham' => 'SP0058'],
            ['So_luong_dat' => 24, 'Don_gia_mua' => 534000]
        );

        // 6.3. Đơn bán ĐANG GIAO HÀNG (NV Kho cập nhật Giao thành công / Giao thất bại)
        DonHangBan::updateOrCreate(
            ['Ma_don_hang_ban' => 'DB-2026-0002'],
            [
                'Ma_khach_hang' => 'KH0012',
                'Ma_kho' => 'HN',
                'Ma_nguoi_lap' => 'ND0005',
                'Thoi_diem_xac_nhan' => date('Y-m-d 09:00:00', strtotime('-1 day')),
                'Ten_nguoi_nhan' => 'Nhà hàng 703 Beer',
                'Sdt_nguoi_nhan' => '0359360383',
                'Dia_chi_giao_hang' => 'G3 Chung cư Vinhomes Green Bay, Mễ Trì, Hà Nội',
                'Tong_tien_hang' => 6 * 368000,
                'Trang_thai' => 'Đang giao hàng',
            ]
        );
        ChiTietDonBan::updateOrCreate(
            ['Ma_don_hang_ban' => 'DB-2026-0002', 'Ma_san_pham' => 'SP0012'],
            ['So_luong' => 6, 'Don_gia_ban' => 368000, 'Chiet_khau' => 0]
        );
        PhieuXuatKho::updateOrCreate(
            ['Ma_phieu_xuat' => 'PX-2026-0002'],
            [
                'Ma_don_hang_ban' => 'DB-2026-0002',
                'Ngay_tao' => date('Y-m-d', strtotime('-1 day')),
                'Trang_thai_giao_hang' => 'Đang giao hàng',
                'Don_vi_van_chuyen' => 'Giao Hàng Nhanh',
                'Ma_van_don' => 'GHN-SW-8821',
                'Ma_nguoi_xuat' => 'ND0003',
                'Thoi_diem_xuat_kho' => date('Y-m-d 13:30:00', strtotime('-1 day')),
            ]
        );
        ChiTietPhieuXuat::updateOrCreate(
            ['Ma_phieu_xuat' => 'PX-2026-0002', 'Ma_lo_phan_bo' => 'L2407', 'Ma_lo_thuc_xuat' => 'L2407'],
            ['So_luong' => 6, 'Gia_von_xuat' => $giaVonMap['SP0012'] ?? 239000]
        );

        // 6.4. Đơn bán GIAO THẤT BẠI (chờ NV Kho xử lý hoàn hàng về kho)
        DonHangBan::updateOrCreate(
            ['Ma_don_hang_ban' => 'DB-2026-0003'],
            [
                'Ma_khach_hang' => 'KH0014',
                'Ma_kho' => 'HN',
                'Ma_nguoi_lap' => 'ND0005',
                'Thoi_diem_xac_nhan' => date('Y-m-d 08:00:00', strtotime('-3 days')),
                'Ten_nguoi_nhan' => 'Công ty Lucky Cat Việt Nam',
                'Sdt_nguoi_nhan' => '0902250587',
                'Dia_chi_giao_hang' => 'Số 27 ngõ 97 Nguyễn Đổng Chi, Nam Từ Liêm, Hà Nội',
                'Tong_tien_hang' => 6 * 535000,
                'Trang_thai' => 'Giao thất bại',
            ]
        );
        ChiTietDonBan::updateOrCreate(
            ['Ma_don_hang_ban' => 'DB-2026-0003', 'Ma_san_pham' => 'SP0018'],
            ['So_luong' => 6, 'Don_gia_ban' => 535000, 'Chiet_khau' => 0]
        );
        PhieuXuatKho::updateOrCreate(
            ['Ma_phieu_xuat' => 'PX-2026-0003'],
            [
                'Ma_don_hang_ban' => 'DB-2026-0003',
                'Ngay_tao' => date('Y-m-d', strtotime('-3 days')),
                'Trang_thai_giao_hang' => 'Giao thất bại',
                'Trang_thai_hoan_hang' => 'Chờ hoàn hàng',
                'Don_vi_van_chuyen' => 'Ahamove',
                'Ma_van_don' => 'AHA-99021',
                'Ma_nguoi_xuat' => 'ND0003',
                'Thoi_diem_xuat_kho' => date('Y-m-d 10:00:00', strtotime('-3 days')),
                'Thoi_diem_ket_qua_giao' => date('Y-m-d 17:00:00', strtotime('-2 days')),
                'Ly_do_giao_that_bai' => 'Người nhận đi công tác đột xuất, hẹn giao lại hoặc hoàn kho.',
            ]
        );
        ChiTietPhieuXuat::updateOrCreate(
            ['Ma_phieu_xuat' => 'PX-2026-0003', 'Ma_lo_phan_bo' => 'L2410', 'Ma_lo_thuc_xuat' => 'L2410'],
            ['So_luong' => 6, 'Gia_von_xuat' => $giaVonMap['SP0018'] ?? 348000]
        );

        // 6.5. Phiếu kiểm kê CHỜ DUYỆT (có chênh lệch để QL Kho duyệt điều chỉnh tồn)
        PhieuKiemKe::updateOrCreate(
            ['Ma_phieu_kiem_ke' => 'KK-2026-0002'],
            [
                'Ma_kho' => 'HN',
                'Ma_nguoi_tao' => 'ND0002',
                'Ma_nguoi_kiem' => 'ND0003',
                'Thoi_diem_tao' => date('Y-m-d 09:00:00', strtotime('-1 day')),
                'Thoi_diem_dem_xong' => date('Y-m-d 16:00:00', strtotime('-1 day')),
                'Trang_thai' => 'Chờ duyệt',
                'Ghi_chu' => 'Kiểm kê đột xuất khu vực vang Chile',
            ]
        );
        ChiTietKiemKe::updateOrCreate(
            ['Ma_phieu_kiem_ke' => 'KK-2026-0002', 'Ma_lo' => 'L2405'],
            [
                'So_luong_he_thong' => 30,
                'So_luong_thuc_te' => 29,
                'Ly_do_chenh_lech' => 'Thiếu 1 chai mẫu mở thử cho khách VIP',
                'Gia_von_dieu_chinh' => $giaVonMap['SP0007'] ?? 137000,
            ]
        );
    }
}

