<style>
    /* Pengaturan Halaman */
    @page {
        margin: 140px 40px 60px 40px;
        /* Atas, Kanan, Bawah, Kiri */
    }

    /* Header Fixed - Akan muncul di setiap halaman */
    header {
        position: fixed;
        top: -120px;
        left: 0;
        right: 0;
        height: 100px;
    }

    /* Footer Fixed */
    footer {
        position: fixed;
        bottom: -40px;
        left: 0;
        right: 0;
        height: 30px;
        font-size: 7pt;
        color: #999;
        text-align: right;
        border-top: 1px solid #eee;
    }

    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 8.5pt;
        line-height: 1.2;
        color: #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    /* Styling Header */
    .header-table td {
        vertical-align: top;
    }

    .company-title {
        font-size: 16pt;
        font-weight: bold;
        text-align: right;
    }

    .company-address {
        font-size: 8pt;
        text-align: right;
    }

    /* Styling Umum */
    .divider {
        border-top: 1.5px solid #000;
        margin: 5px 0 15px 0;
    }

    .section-title {
        font-size: 9pt;
        font-weight: bold;
        border-bottom: 1px solid #000;
        margin-bottom: 5px;
    }

    .recipient-box {
        background-color: #f8f9fa;
        padding: 5px;
        font-size: 8.5pt;
    }

    .po-box-title {
        font-size: 14pt;
        font-weight: bold;
        text-align: center;
        border-bottom: 1.5px solid #000;
        margin-bottom: 5px;
    }

    /* Tabel Barang */
    .items-table {
        margin-top: 10px;
        width: 100%;
    }

    .items-table th {
        background-color: #1a446c;
        color: #fff;
        padding: 5px;
        font-size: 8.5pt;
    }

    .items-table td {
        padding: 4px;
        border-bottom: 1px solid #ddd;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .summary-table {
        width: 100%;
        background-color: #f8f9fa;
        margin-top: 10px;
    }

    .summary-table td {
        padding: 3px 10px;
    }

    .keterangan-content {
        white-space: pre-line;
        /* Ini kuncinya: agar enter/newline tetap terbaca */
        font-size: 8pt;
        margin-top: 5px;
    }
</style>
<style>
    /* Tambahkan atau perbarui CSS ini */
    .total-row {
        background-color: #1a446c !important;
        /* Biru */
        color: #ffffff !important;
        /* Tulisan putih */
        font-weight: bold;
    }

    /* Agar teks di dalam total row terlihat jelas */
    .total-row td {
        padding: 8px 10px !important;
    }

    /* Penting: Memaksa header tabel berulang di setiap halaman */
    thead {
        display: table-header-group;
    }

    /* Opsional: Jika tabel terpotong di tengah baris, cegah dengan ini */
    tr {
        page-break-inside: avoid;
    }
</style>
