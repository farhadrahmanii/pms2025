<form wire:submit.prevent="submit" class="dark:bg-gray-500">
    {{ $this->form }}

    <div class="dialog-buttons">
        <button type="submit" wire:loading.attr="disabled">
            {{ __('Save') }}
        </button>
        <button type="button" wire:click="cancel" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </button>
    </div>
</form>