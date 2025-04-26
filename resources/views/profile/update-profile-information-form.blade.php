<x-form-section submit="updateProfileInformation">
  <x-slot name="title">
    {{ __('Profile Information') }}
  </x-slot>

  <x-slot name="description">
    {{ __('Update your account\'s profile information and email address.') }}
  </x-slot>

  <x-slot name="form">
    <!-- Name -->
    <div class="col-span-6 sm:col-span-4">
      <x-label for="name" value="{{ __('Name') }}" />
      <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required
        autocomplete="name" />
      <x-input-error for="name" class="mt-2" />
    </div>

    <!-- Email -->
    <div class="col-span-6 sm:col-span-4">
      <x-label for="email" value="{{ __('Email') }}" />
      <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required
        autocomplete="username" />
      <x-input-error for="email" class="mt-2" />

      @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
              !$this->user->hasVerifiedEmail())
        <p class="mt-2 text-sm dark:text-white">
          {{ __('Your email address is unverified.') }}

          <button type="button"
            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
            wire:click.prevent="sendEmailVerification">
            {{ __('Click here to re-send the verification email.') }}
          </button>
        </p>

        @if ($this->verificationLinkSent)
          <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to your email address.') }}
          </p>
        @endif
      @endif
    </div>

    <div class="col-span-6 flex flex-row gap-3 sm:col-span-4">
      <!-- Gender -->
      <div class="w-full">
        <x-label for="gender" value="{{ __('Gender') }}" />
        <x-select id="gender" class="mt-1 block w-full" wire:model="state.gender" required>
          <option value="male" {{ $state['gender'] == 'male' ? 'selected' : '' }}>
            {{ __('Male') }}
          </option>
          <option value="female" {{ $state['gender'] == 'female' ? 'selected' : '' }}>
            {{ __('Female') }}
          </option>
        </x-select>
        <x-input-error for="gender" class="mt-2" />
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <x-action-message class="me-3" on="saved">
      {{ __('Saved.') }}
    </x-action-message>

    <x-button wire:loading.attr="disabled" wire:target="photo">
      {{ __('Save') }}
    </x-button>
  </x-slot>
</x-form-section>