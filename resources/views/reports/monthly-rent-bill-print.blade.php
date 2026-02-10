<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monthly Rent Bill Report</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        html, body { background: #fff; color: #000; }
        body { font-family: "Times New Roman", Times, serif; margin: 0; }
        * { color: #000; background: #fff; }
        .print-actions { margin: 10px 0 0; text-align: right; }
        @media print { .print-actions { display: none; } }
        .print-header { position: fixed; left: 0; right: 0; top: 10mm; }
        .print-title { text-align: center; margin-top: 0; }
        .print-title h1 { font-size: 28px; font-weight: 700; margin: 0; letter-spacing: 1px; }
        .print-title p { margin: 2px 0; font-size: 12px; }
        .print-pill { display: inline-block; margin-top: 8px; padding: 4px 14px; border-radius: 999px; background: #000; color: #fff; font-weight: 700; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
        .print-table th, .print-table td { border: 1px solid #000; padding: 4px 6px; }
        .print-table th { text-align: center; font-weight: 700; background: #fff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .print-footer { position: fixed; left: 0; right: 0; bottom: 10mm; display: flex; justify-content: space-between; font-size: 12px; }
        .print-sign { width: 160px; border-top: 1px solid #000; padding-top: 6px; text-align: center; }
        .print-body { padding-top: 50mm; padding-bottom: 30mm; }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print / PDF</button>
    </div>
    <div class="print-header">
        <div class="print-title">
        <h1>IIUC TOWER</h1>
        <p>1700/A, Plot#9, Agrabad C/A Sk. Mujib Road</p>
        <p>Chittagong, Bangladesh. Phone : 880-31-252 12 00</p>
        <span class="print-pill">Monthly Report {{ $month->format('F Y') }}</span>
        </div>
    </div>

    <div class="print-body">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 50px;">Serial</th>
                <th>Name of The Company</th>
                <th style="width: 80px;">Month</th>
                <th style="width: 60px;">Year</th>
                <th style="width: 90px;">Rent Amount</th>
                <th style="width: 90px;">Car Parking</th>
                <th style="width: 90px;">Total Rent</th>
                <th style="width: 80px;">Income Tax</th>
                <th style="width: 90px;">Adjusted Advance</th>
                <th style="width: 100px;">Received Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->client_name }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->bill_month)->format('F') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->bill_month)->format('Y') }}</td>
                    <td class="text-right">{{ number_format((float) $row->rent, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->parking_total, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->total, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->income_tax, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->rent_advance, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->grand_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="10">No bills found for this month.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="4" class="text-center">Totals</th>
                    <th class="text-right">{{ number_format((float) ($totals['rent'] ?? 0), 2) }}</th>
                    <th class="text-right">{{ number_format((float) ($totals['parking_total'] ?? 0), 2) }}</th>
                    <th class="text-right">{{ number_format((float) ($totals['total'] ?? 0), 2) }}</th>
                    <th class="text-right">{{ number_format((float) ($totals['income_tax'] ?? 0), 2) }}</th>
                    <th class="text-right">{{ number_format((float) ($totals['rent_advance'] ?? 0), 2) }}</th>
                    <th class="text-right">{{ number_format((float) ($totals['grand_total'] ?? 0), 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
    </div>

    <div class="print-footer">
        <div class="print-sign">Prepared by</div>
        <div class="print-sign">Approved by</div>
    </div>
</body>
</html>
