<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>A-Sync - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style>
            body {
                background-color: #f3f4f6; /* Match example's light gray background */
            }
            </style>
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="min-h-screen flex items-center justify-center">
            <div class="w-full max-w-6xl mx-auto grid md:grid-cols-2 items-center">

                <!-- Left Column: Info & Login Form -->
                <div class="bg-white p-8 sm:p-12 md:p-16 lg:p-20 h-full flex flex-col justify-center">
                    <div class="mb-auto">
                        <h2 class="text-2xl font-bold text-blue-600">A-Sync</h2>
                    </div>

                    <div class="my-auto">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-3">
                            Streamlining School Operations
                        </h1>
                        <p class="text-gray-600 mb-8">Welcome! Please login to your account.</p>

                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Username -->
                            <div class="mb-4">
                                <label for="username" class="block font-medium text-sm text-gray-700 mb-1">{{ __('Username') }}</label>
                                <input id="username" class="block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder-gray-400" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Enter your username" />
                                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label for="password" class="block font-medium text-sm text-gray-700 mb-1">{{ __('Password') }}</label>
                                <input id="password" class="block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 placeholder-gray-400" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"/>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between mb-6">
                                <label for="remember_me" class="inline-flex items-center">
                                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                        @endif
                                </div>

                            <div class="mt-6">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-cta-yellow border border-transparent rounded-md font-semibold text-base text-gray-800 uppercase tracking-wider hover:bg-cta-yellow-dark active:bg-cta-yellow-dark focus:outline-none focus:border-cta-yellow-dark focus:ring ring-cta-yellow disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </form>
                                        </div>

                    <div class="mt-auto text-center">
                        <p class="text-xs text-gray-500">
                            Lyceum of Alabang - A-Sync Workflow System &copy; {{ date('Y') }}
                                            </p>
                                        </div>
                                    </div>

                <!-- Right Column: Image -->
                <div class="hidden md:block md:h-screen">
                     <img src="{{ asset('images/lyceum-logo.png') }}" alt="Lyceum Logo" class="w-full h-full object-cover">
                </div>

            </div>
        </div>
    </body>
</html>