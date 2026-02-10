<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenant Invoice</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        html, body { background: #fff; color: #000; }
        body { font-family: "Times New Roman", Times, serif; margin: 0; }
        * { color: #000; background: #fff; }
        .print-actions { margin: 10px 0 0; text-align: right; }
        @media print { .print-actions { display: none; } }
        .print-title { text-align: center; margin-top: 8px; }
        .print-title h1 { font-size: 28px; font-weight: 700; margin: 0; letter-spacing: 1px; }
        .print-title p { margin: 2px 0; font-size: 12px; }
        .print-pill { display: inline-block; margin-top: 8px; padding: 4px 14px; border-radius: 999px; background: #000; color: #fff; font-weight: 700; }
        .tenant-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 30px;
            margin: 14px 0 18px;
            font-size: 13px;
        }
        .tenant-row { display: flex; gap: 6px; }
        .tenant-row strong { width: 140px; }
        .section-title { font-size: 14px; font-weight: 700; margin: 12px 0 6px; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
        .print-table th, .print-table td { border: 1px solid #000; padding: 4px 6px; }
        .print-table th { text-align: center; font-weight: 700; background: #fff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .print-footer { margin-top: 28px; display: flex; justify-content: space-between; font-size: 12px; }
        .print-sign { width: 160px; border-top: 1px solid #000; padding-top: 6px; text-align: center; }
    </style>
</head>

<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print / PDF</button>
    </div>

    <div class="print-title">
        <h1>IIUC TOWER</h1>
        <p>1700/A, Plot#9, Agrabad C/A Sk. Mujib Raod</p>
        <p>Chittagong, Bangladesh. Phone : 880-31-252 12 00</p>
        <span class="print-pill">Rent for the Month of {{ optional($rentbill->bill_month)->format('F Y') }}</span>
    </div>

    <div class="section-title">Tenant Details</div>
    <strong>Invoice ID:</strong> {{ $rentbill->invoice_id }}
    <strong>Date:</strong> {{ ($rentbill->created_at)->format('d, m, Y') }}
    <strong>Tenant Name:</strong> To M/s. Mr. {{ $rentbill->client_name }}
    

    <table class="" border="1" padding="10px">
        <tr>
            <td>
            @php
                $items = collect($rentbill->rent_items ?? []);
                $sfts = $items->pluck('sft')->map(fn ($v) => (float) $v)->filter()->values();
                $rates = $items->pluck('rate')->map(fn ($v) => (float) $v)->filter()->values();

                $sftText = $sfts->map(fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.'))->implode(' and ');
                $rateText = $rates->map(fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.'))->implode(' and ');
                $billMonth = optional($rentbill->bill_month)->format('F, Y');
            @endphp

            <p>
                Rent for {{ $sftText }} space of {{ $rentbill->floor ?? ($rentbill->tenant->floor ?? '-') }} Floor at IIUC Tower,
                1700/A, Plot #9, SK. Mujib Road, Agrabad C/A, Ctg.
                Rate {{ $rateText }} TK per sft, per month for the month of {{ $billMonth }}.
            </p>


            </td>
            <td>
               {{ number_format((float) $rentbill->rent, 2) }} BDT 
            </td>
        </tr>
        <tr>
            <td>Car Parking Rent (If any)</td>
            <td>{{ number_format((float) $rentbill->parking_total, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Other Cost</td>
            <td>{{ number_format((float) $rentbill->others_cost, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Total</td>
            <td>{{ number_format((float) $rentbill->total, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Less {{ number_format((float) $rentbill->tax_percent, 1) }} % Income Tax </td>
            <td>{{ number_format((float) $rentbill->income_tax, 2) }} BDT </td>
        </tr>
        <tr>
            <td>Balance</td>
            <td>{{ number_format((float) $rentbill->balance, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Less Adjustment of Propertionate Advance Rent</td>
            <td>{{ number_format((float) $rentbill->rent_advance, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Amount to be Paid</td>
            <td>{{ number_format((float) $rentbill->amount_to_pay, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Add {{ number_format((float) $rentbill->vat_percent, 1) }} % Vat</td>
            <td> {{ number_format((float) $rentbill->vat_total, 2) }} BDT</td>
        </tr>
        <tr>
            <td>Grand total</td>
            <td>{{ number_format((float) $rentbill->grand_total, 2) }} BDT</td>
        </tr>
    </table>

    <div class="print-footer">
        <div class="print-sign">Prepared by</div>
        <div class="print-sign">Approved by</div>
    </div>

</body>
</html>
