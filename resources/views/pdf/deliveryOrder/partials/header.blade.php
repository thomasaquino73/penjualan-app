<header>
    <table class="header-table">
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <img src="{{ public_path('image/logo/logo_print_dotmatrix.bmp') }}" style="width:270px; height:100px">
            </td>

            <td style="width:50%; text-align:right; vertical-align:middle;">
                <div class="company-title">
                    {{ $company->nama_perusahaan }}
                </div>

                <div class="company-address" style="width:'90px'">
                    {!! nl2br(e($company->alamat)) !!}
                </div>
            </td>
        </tr>
    </table>
</header>
