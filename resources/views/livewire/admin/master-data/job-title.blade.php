<div>
    <div class="mb-4 flex-col items-center gap-5 sm:flex-row md:flex md:justify-between lg:mr-4">
        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200 md:mb-0">
            Data Jabatan
        </h3>
        <x-button wire:click="showCreating">
            <x-heroicon-o-plus class="mr-2 h-4 w-4" /> Tambah Jabatan
        </x-button>
    </div lass="overflow-x-scroll">
    <table class="w-full overflow-hidden rounded-t-lg">
        <thead style="background-color:rgb(176, 201, 251);">
            <tr>
                <th scope="col"
                    class="px-12 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 width-20">
                    Jabatan
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
            @foreach ($jobTitles as $jobTitle)
                <tr>
                    <td class="text-left mr-2 px-12 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $jobTitle->name }}
                    </td>
                    <td class="whitespace-nowrap text-center">
                        <div class="flex justify-center space-x-2">
                            <x-edit-button wire:click="edit({{ $jobTitle->id }})">
                                Edit
                            </x-edit-button>
                            <x-delete-button wire:click="confirmDeletion({{ $jobTitle->id }}, '{{ $jobTitle->name }}')">
                                Hapus
                            </x-delete-button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Hapus Jabatan
        </x-slot>

        <x-slot name="content">
            Apakah Anda yakin ingin menghapus <b>{{ $deleteName }}</b>?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                {{ __('Confirm') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <x-dialog-modal wire:model="creating">
        <x-slot name="title">
            Jabatan Baru
        </x-slot>

        <form wire:submit="create">
            <x-slot name="content">
                <x-label for="name">Nama Jabatan</x-label>
                <x-input id="name" class="mt-1 block w-full" type="text" wire:model="name" />
                @error('name')
                    <x-input-error for="name" class="mt-2" message="{{ $message }}" />
                @enderror
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('creating')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="create" wire:loading.attr="disabled">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-dialog-modal wire:model="editing">
        <x-slot name="title">
            Edit Jabatan
        </x-slot>

        <form wire:submit.prevent="update">
            <x-slot name="content">
                <x-label for="name">Nama Jabatan</x-label>
                <x-input id="name" class="mt-1 block w-full" type="text" wire:model="name" />
                @error('name')
                    <x-input-error for="name" class="mt-2" message="{{ $message }}" />
                @enderror
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('editing')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-2" wire:click="update" wire:loading.attr="disabled">
                    {{ __('Confirm') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>
</div>
