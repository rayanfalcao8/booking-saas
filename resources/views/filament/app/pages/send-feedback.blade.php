<x-filament-panels::page>
    <div class="max-w-3xl">
        <form wire:submit="send" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit">
                Envoyer mon retour
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
