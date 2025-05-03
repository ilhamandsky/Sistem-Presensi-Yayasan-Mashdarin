<div>
  <div class="mb-4 flex-col items-center gap-5 sm:flex-row md:flex md:justify-between lg:mr-4">
    <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200 md:mb-0">
      Data Karyawan
    </h3>
    <x-button wire:click="showCreating">
      <x-heroicon-o-plus class="mr-2 h-4 w-4" /> Tambah Karyawan
    </x-button>
  </div>
  <div class="mb-1 text-sm dark:text-white">Filter:</div>
  <div class="mb-4 grid grid-cols-3 flex-wrap items-center gap-5 md:gap-8 lg:flex">
    <x-select id="jobTitle" wire:model.live="jobTitle">
      <option value="">{{ __('Select Job Title') }}</option>
      @foreach (App\Models\JobTitle::all() as $_jobTitle)
        <option value="{{ $_jobTitle->id }}">
          {{ $_jobTitle->name }}
        </option>
      @endforeach
    </x-select>

    <div class="col-span-3 flex items-center gap-2 lg:col-span-1">
      <x-input type="text" class="w-full lg:w-72" name="search" id="search" wire:model="search" placeholder="{{ __('Search') }}" />
      <div class="flex gap-2">
        <x-button class="flex justify-center sm:w-32" type="button" wire:click="$refresh" wire:loading.attr="disabled">
          {{ __('Search') }}
        </x-button>
        @if ($search)
          <x-secondary-button class="flex justify-center sm:w-32" type="button" wire:click="$set('search', '')" wire:loading.attr="disabled">
            {{ __('Reset') }}
          </x-secondary-button>
        @endif
      </div>
    </div>
  </div>

  <div class="overflow-x-scroll">
    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">
            No.
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Nama') }}
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Jabatan') }}
          </th>
          <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300">
            {{ __('Actions') }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
        @foreach ($users as $user)
          <tr wire:key="{{ $user->id }}" class="group">
            <td class="p-2 text-center text-sm text-gray-900 dark:text-white">
              {{ $loop->iteration }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white cursor-pointer" wire:click="show('{{ $user->id }}')">
              {{ $user->name }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white cursor-pointer" wire:click="show('{{ $user->id }}')">
              {{ optional($user->jobTitle)->name ?? '-' }}
            </td>
            <td class="px-6 py-4 text-right">
              <x-button wire:click="edit('{{ $user->id }}')">
                Edit
              </x-button>
              <x-danger-button wire:click="confirmDeletion('{{ $user->id }}', '{{ $user->name }}')">
                Hapus
              </x-danger-button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-3">
    {{ $users->links() }}
  </div>

  <x-confirmation-modal wire:model="confirmingDeletion">
    <x-slot name="title">
      Hapus Karyawan
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
      Karyawan Baru
    </x-slot>
    <form wire:submit="create">
      <x-slot name="content">
        <div class="mt-4">
          <x-label for="name">Nama Karyawan</x-label>
          <x-input id="name" class="mt-1 block w-full" type="text" wire:model="form.name" />
          @error('form.name')
            <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
        <div class="mt-4">
          <x-label for="gender">{{ __('Gender') }}</x-label>
          <div class="my-3 flex flex-row gap-5">
            <div class="flex items-center">
              <input type="radio" id="gender-male" wire:model="form.gender" value="male" />
              <x-label for="gender-male" class="ml-2">{{ __('Male') }}</x-label>
            </div>
            <div class="flex items-center">
              <input type="radio" id="gender-female" wire:model="form.gender" value="female" />
              <x-label for="gender-female" class="ml-2">{{ __('Female') }}</x-label>
            </div>
          </div>
          @error('form.gender')
            <x-input-error for="form.gender" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
        <div class="mt-4">
          <x-label for="form.job_title_id" value="{{ __('Job Title') }}" />
          <x-select id="form.job_title_id" class="mt-1 block w-full" wire:model="form.job_title_id">
            <option value="">{{ __('Select Job Title') }}</option>
            @foreach (App\Models\JobTitle::all() as $jobTitle)
              <option value="{{ $jobTitle->id }}" {{ $jobTitle->id == $form->job_title_id ? 'selected' : '' }}>
                {{ $jobTitle->name }}
              </option>
            @endforeach
          </x-select>
          @error('form.job_title_id')
            <x-input-error for="form.job_title_id" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
      </x-slot>
      <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('creating')" wire:loading.attr="disabled">
          {{ __('Cancel') }}
        </x-secondary-button>
        <x-button class="ml-2" wire:click="create" wire:loading.attr="disabled" wire:target="form.photo">
          {{ __('Confirm') }}
        </x-button>
      </x-slot>
    </form>
  </x-dialog-modal>

  <x-dialog-modal wire:model="editing">
    <x-slot name="title">
      Edit Karyawan
    </x-slot>
    <form wire:submit.prevent="update" id="user-edit">
      <x-slot name="content">
        <div class="mt-4">
          <x-label for="name">Nama Karyawan</x-label>
          <x-input id="name" class="mt-1 block w-full" type="text" wire:model="form.name" />
          @error('form.name')
            <x-input-error for="form.name" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
        <div class="mt-4">
          <x-label for="gender">{{ __('Gender') }}</x-label>
          <div class="my-3 flex flex-row gap-5">
            <div class="flex items-center">
              <input type="radio" id="gender-male" wire:model="form.gender" value="male" />
              <x-label for="gender-male" class="ml-2">{{ __('Male') }}</x-label>
            </div>
            <div class="flex items-center">
              <input type="radio" id="gender-female" wire:model="form.gender" value="female" />
              <x-label for="gender-female" class="ml-2">{{ __('Female') }}</x-label>
            </div>
          </div>
          @error('form.gender')
            <x-input-error for="form.gender" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
        <div class="mt-4">
          <x-label for="form.job_title_id" value="{{ __('Job Title') }}" />
          <x-select id="form.job_title_id" class="mt-1 block w-full" wire:model="form.job_title_id">
            <option value="">{{ __('Select Job Title') }}</option>
            @foreach (App\Models\JobTitle::all() as $jobTitle)
              <option value="{{ $jobTitle->id }}" {{ $jobTitle->id == $form->job_title_id ? 'selected' : '' }}>
                {{ $jobTitle->name }}
              </option>
            @endforeach
          </x-select>
          @error('form.job_title_id')
            <x-input-error for="form.job_title_id" class="mt-2" message="{{ $message }}" />
          @enderror
        </div>
      </x-slot>
      <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('editing')" wire:loading.attr="disabled">
          {{ __('Cancel') }}
        </x-secondary-button>
        <x-button class="ml-2" wire:click="update" wire:loading.attr="disabled" wire:target="form.photo">
          {{ __('Confirm') }}
        </x-button>
      </x-slot>
    </form>
  </x-dialog-modal>

  <x-modal wire:model="showDetail">
    @if ($form->user)
      <div class="px-6 py-4">
        <div class="my-4 flex items-center justify-center">
          <img class="h-32 w-32 rounded-full object-cover" src="{{ $form->user->profile_photo_url }}" alt="{{ $form->user->name }}" />
        </div>
        <div class="text-center text-lg font-medium text-gray-900 dark:text-gray-100">
          {{ $form->user->name }}
        </div>
        <div class="mt-4">
          <x-label for="gender" value="{{ __('Gender') }}" />
          <p>{{ __($form->user->gender) }}</p>
        </div>
        <div class="mt-4">
          <x-label for="job_title_id" value="{{ __('Job Title') }}" />
          <p>{{ optional($form->user->jobTitle)->name ?? '-' }}</p>
        </div>
      </div>
    @endif
  </x-modal>
</div>
