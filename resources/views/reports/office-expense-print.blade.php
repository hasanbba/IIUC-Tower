<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Office Expense Report</title>
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
            <span class="print-pill">Bill Summary</span>
            <div style="margin-top: 6px; font-size: 12px; text-align: right;">
                Date : {{ $group->expense_date?->format('d/m/Y') }}
            </div>
        </div>
    </div>

    @php
        $used = $group->items
            ->groupBy('head_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'head_id' => $first?->head?->id,
                    'head_code' => $first?->head?->code ?? '',
                    'head_name' => $first?->head?->name ?? '',
                    'total' => $items->sum('amount'),
                    'remark' => $items->pluck('remark')->filter()->implode(', '),
                ];
            });

        $allHeads = \App\Models\OfficeExpenseHead::orderBy('code')->get();
    @endphp

    <div class="print-body">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 60px;">Sl No.</th>
                <th style="width: 90px;">Acc.Code</th>
                <th>Category</th>
                <th style="width: 120px;">Amount</th>
                <th style="width: 160px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allHeads as $head)
                @php
                    $row = $used->get($head->id);
                    $amount = $row['total'] ?? 0;
                    $remark = $row['remark'] ?? '';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $head->code }}</td>
                    <td>{{ $head->name }}</td>
                    <td class="text-right">{{ number_format((float) $amount, 2) }}</td>
                    <td>{{ $remark }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total Expenses:</th>
                <th class="text-right">{{ number_format((float) $group->total_amount, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
        @php
            $total = (float) $group->total_amount;
            $words = null;
            if (class_exists(\NumberFormatter::class)) {
                $fmt = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                $words = $fmt->format($total);
            }
        @endphp
        <div style="margin-top: 8px; font-size: 12px;">
            Taka in words: {{ $words ? ucfirst($words) : number_format($total, 2) }}
        </div>
    </div>

    <div class="print-footer">
        <div class="print-sign">Receive Sign</div>
        <div class="print-sign">Head of division</div>
        <div class="print-sign">Director,ACFD</div>
    </div>
</body>
</html>
