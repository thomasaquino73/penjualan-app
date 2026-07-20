<hr>

<table style="border:none">

    <tr>

        <td style="border:none">

            Dicetak :

            {{ now()->format('d-m-Y H:i') }}

        </td>

        <td style="border:none;text-align:right">

            <script type="text/php">
if (isset($pdf)) {

    $font = $fontMetrics->getFont("Helvetica","normal");

    $pdf->page_text(
        340,
        565,
        "Halaman {PAGE_NUM} / {PAGE_COUNT}",
        $font,
        8
    );

}
</script>

        </td>

    </tr>

</table>
