<header>
        <table class="header-table">
            <tr>
                <td>
                    <img src="{{ public_path('image/logo/logo_print.png') }}" style="width: 330px;" height="120px"
                        alt="Logo Perusahaan">
                </td>
                <td>
                    <div class="company-title">{{ $company->nama_perusahaan }}</div>
                    <div class="company-address">{{ $company->alamat }}</div>
                </td>
            </tr>
        </table>
        <div class="divider"></div>
    </header>