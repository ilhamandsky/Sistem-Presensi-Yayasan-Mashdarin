@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="flex flex-col md:flex-row w-full max-w-4xl bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Bagian Kiri -->
            <div
                class="w-1/2 relative flex flex-col justify-center px-12 bg-gradient-to-br from-blue-700 to-blue-500 text-white rounded-l-2xl">
                <h1 class="text-4xl font-bold drop-shadow">SiMash</h1>
                <p class="text-lg mt-2 drop-shadow">Sistem Presensi Digital Terpercaya</p>
                <div class="absolute w-64 h-64 border-2 border-white/20 rounded-full bottom-10 left-10"></div>
            </div>

            <!-- Bagian Kanan (Form Login) -->
            <div class="w-1/2 bg-white p-10 rounded-r-2xl shadow-lg">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('logo.jpg') }}" alt="Logo" class="h-32 w-30">
                </div>

                <h3 class="text-xl font-semibold mb-1">Hello Again!</h3>
                <p class="text-sm text-gray-500 mb-6">Selamat Datang Kembali</p>

                @if (session('status'))
                    <div class="mb-4 text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="relative">
                            <input id="email" name="email" type="text" autocomplete="email"
                                value="{{ old('email') }}"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2" />
                        </div>
                        @error('email')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2" />
                        </div>
                        @error('password')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center mb-4">
                        <input id="remember_me" name="remember" type="checkbox"
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
                    </div>

                    <button type="submit"
                        class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition duration-150 ease-in-out">
                        Masuk
                    </button>

                    @if (Route::has('password.request'))
                        <div class="mt-4 text-sm">
                            <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">Lupa kata
                                sandi Anda?</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
