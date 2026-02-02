<x-filament-panels::page>
    {{ $this->form }}

    <div class="mt-4 flex gap-2">
        <x-filament::button type="submit" wire:click.prevent="generate(true)">
            Generate Single Bill
        </x-filament::button>

        <x-filament::button type="submit" color="success" wire:click.prevent="generate(false)">
            Generate All Bills
        </x-filament::button>
    </div>
</x-filament-panels::page>