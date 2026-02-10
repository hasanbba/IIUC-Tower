<x-filament-panels::page>
    <style>
        .screen-only { display: block; }
        .print-only { display: none; }
        .report-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 2px 0;
        }
        .report-subtitle {
            color: #6b7280;
            font-size: 12px;
        }

        @media print {
            .screen-only { display: none !important; }
            .print-only { display: block !important; }
            * { color: #000 !important; background: #fff !important; }
            .fi-topbar, .fi-sidebar, .fi-header, .fi-breadcrumbs, .fi-footer { display: none !important; }
            .fi-page, .fi-main { padding: 0 !important; }
            table { width: 100% !important; }
        }

        @page {
            
            margin: 12mm;
        }

        .print-title { font-family: "Times New Roman", Times, serif; text-align: center; margin-top: 8px; }
        .print-title h1 { font-size: 28px; font-weight: 700; margin: 0; letter-spacing: 1px; }
        .print-title p { margin: 2px 0; font-size: 12px; }
        .print-pill { display: inline-block; margin-top: 8px; padding: 4px 14px; border-radius: 999px; background: #000; color: #fff !important; font-weight: 700; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
        .print-table th, .print-table td { border: 1px solid #000; padding: 4px 6px; }
        .print-table th { text-align: center; font-weight: 700; background: #fff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .print-footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 12px; }
        .print-sign { width: 160px; border-top: 1px solid #000; padding-top: 6px; text-align: center; }
    </style>
    <div class="screen-only report-card">
        <div class="flex items-end gap-4 flex-wrap">
            <div class="flex-1 min-w-[100px]">
                {{ $this->form }}
            </div>
            @php
                $printUrl = route('reports.monthly-rent-bill.print', [
                    'bill_month' => $this->data['bill_month'] ?? null,
                ]);
            @endphp
            <x-filament::button tag="a" :href="$printUrl" target="_blank">
                Print / PDF
            </x-filament::button>
        </div>
    </div>

    <div id="print-area" class="mt-6 screen-only report-card">
        <div class="report-title">
            Monthly Rent Bill Report
        </div>
        @if (! empty($this->data['bill_month']))
            <div class="report-subtitle">
                Month: {{ \Carbon\Carbon::parse($this->data['bill_month'])->format('F Y') }}
            </div>
        @endif

        <div class="mt-4">
            {{ $this->table }}
        </div>
    </div>

    <div class="print-only"></div>
</x-filament-panels::page>
