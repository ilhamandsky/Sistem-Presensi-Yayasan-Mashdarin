<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
      {{ __('Profile') }}
    </h2>
  </x-slot>

  <div>
    <div class="mx-auto max-w-7xl py-10 sm:px-6 lg:px-8">
      @if (Laravel\Fortify\Features::canUpdateProfileInformation())
        @livewire('profile.update-profile-information-form')

        <x-section-border />
      @endif

      @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <div class="mt-10 sm:mt-0">
          @livewire('profile.update-password-form')
        </div>

        <x-section-border />
      @endif


      @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        
      @endif
    </div>
  </div>
</x-app-layout>
