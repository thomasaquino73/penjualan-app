<header>

    <table class="w-100 header-table">
        <tr>
            <td style="width: 55%;">
                <table>
                    <tr>
                        <td class="logo-box">
                            <img src="{{ public_path('image/logo/logo_print.png') }}" style="width: 340px;">

                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                <div class="company-title">{{ $company->nama_perusahaan }}</div>
                <div class="company-address">
                    {{ $company->alamat }}
                </div>
            </td>
        </tr>
    </table>
</header>
