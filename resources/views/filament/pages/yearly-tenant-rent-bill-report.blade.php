<x-filament-panels::page>
    {{ $this->form }}
    <div class="mt-4">
        @php
            $printUrl = route('reports.yearly-tenant-rent-bill.print', [
                'tenant_id' => $this->data['tenant_id'] ?? null,
                'year' => $this->data['year'] ?? null,
            ]);
        @endphp
        <x-filament::button tag="a" :href="$printUrl" target="_blank">
            Print / PDF
        </x-filament::button>
    </div>

    <div class="mt-6">
        <div class="text-lg font-semibold">
            Tenant Yearly Rent Bills
        </div>
        @if (! empty($this->data['year']))
            <div class="text-sm text-gray-500">
                Year: {{ $this->data['year'] }}
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
