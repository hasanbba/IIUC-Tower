@php
    use App\Filament\Pages\MonthlyRentBillReport;
    use App\Filament\Pages\RentBillGenerator;
    use App\Filament\Pages\YearlyTenantRentBillReport;
@endphp

<x-filament::section>
    <x-slot name="heading">
        <div style="text-align: center;">
        Quick Actions
        </div>
    </x-slot>

    <div class="grid gap-5 grid-cols-1 sm:grid-cols-3" style="text-align: center;">
        <x-filament::button
            tag="a"
            color="warning"
            class="w-full justify-center"
            :href="RentBillGenerator::getUrl()"
        >
            Generate Bill
        </x-filament::button>

        <x-filament::button
            tag="a"
            color="success"
            class="w-full justify-center"
            style="margin:10px ;"
            :href="MonthlyRentBillReport::getUrl()"
        >
            Monthly Bill Report
        </x-filament::button>

        <x-filament::button
            tag="a"
            color="info"
            class="w-full justify-center"
            :href="YearlyTenantRentBillReport::getUrl()"
        >
            Yearly Bill Report
        </x-filament::button>
    </div>
</x-filament::section>
