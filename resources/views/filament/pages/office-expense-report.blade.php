<x-filament-panels::page>
    {{ $this->form }}

    <div class="mt-4">
        @if ($this->group)
            @php
                $printUrl = route('reports.office-expense.print', [
                    'bill_no' => $this->group->bill_no,
                ]);
            @endphp
            <x-filament::button tag="a" :href="$printUrl" target="_blank">
                Print / PDF
            </x-filament::button>
        @endif
    </div>

    <div class="mt-6">
        @if (! $this->group)
            <div class="text-sm text-gray-500">Search by bill number to see details.</div>
        @else
            <div class="flex items-center justify-between">
                <div class="text-lg font-semibold">Bill Summary</div>
                <div class="text-sm text-gray-500">
                    Date: {{ $this->group->expense_date?->format('d/m/Y') }}
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="fi-ta-table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="fi-ta-header-cell text-left">Sl No.</th>
                            <th class="fi-ta-header-cell text-left">Acc.Code</th>
                            <th class="fi-ta-header-cell text-left">Category</th>
                            <th class="fi-ta-header-cell text-right">Amount</th>
                            <th class="fi-ta-header-cell text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->groupedItems as $item)
                            <tr>
                                <td class="fi-ta-cell">{{ $loop->iteration }}</td>
                                <td class="fi-ta-cell">{{ $item['head_code'] ?? '' }}</td>
                                <td class="fi-ta-cell">{{ $item['head_name'] ?? '' }}</td>
                                <td class="fi-ta-cell text-right">{{ number_format((float) ($item['total'] ?? 0), 2) }}</td>
                                <td class="fi-ta-cell">{{ $item['remark'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="fi-ta-cell text-center text-gray-500" colspan="5">
                                    No expense items found for this bill.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="fi-ta-cell text-right font-semibold" colspan="3">Total Expenses:</td>
                            <td class="fi-ta-cell text-right font-semibold">{{ number_format((float) $this->group->total_amount, 2) }}</td>
                            <td class="fi-ta-cell"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
