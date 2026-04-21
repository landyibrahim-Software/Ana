<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4">
        <!-- Background decorative elements -->
        <div class="absolute top-10 left-10 w-64 h-64 bg-gradient-to-br from-indigo-100/50 to-transparent rounded-full blur-xl"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-gradient-to-tr from-purple-100/50 to-transparent rounded-full blur-xl"></div>
        
        <x-auth-card class="w-full max-w-md shadow-2xl rounded-2xl bg-white/95 backdrop-blur-sm p-8 border border-gray-100 relative z-10">
            <x-slot name="logo">
                <div class="flex flex-col items-center mb-6">
                    <div class="mb-4 p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-md">
                        <a href="/">
                            <x-application-logo class="w-16 h-16 text-white" />
                        </a>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">دروستکردنی ئەکاونت</h1>
                    <p class="text-gray-600 text-sm mt-1"> Anna Group</p>
                </div>
            </x-slot>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 font-medium mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <x-text-input
                            id="name"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2.5"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                            placeholder="John Doe"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-medium mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2.5"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            placeholder="john@example.com"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Phone -->
                <div>
                    <x-input-label for="phone" :value="__('Phone Number')" class="text-gray-700 font-medium mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <x-text-input
                            id="phone"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2.5"
                            type="text"
                            name="phone"
                            :value="old('phone')"
                            required
                            placeholder="+1 234 567 8900"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <x-text-input
                            id="password"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2.5"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-medium mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <x-text-input
                            id="password_confirmation"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pl-10 pr-4 py-2.5"
                            type="password"
                            name="password_confirmation"
                            required
                            placeholder="••••••••"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start mt-4">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" required class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-gray-700">
                            I agree to the 
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Terms</a> 
                            and 
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between mt-6 pt-5 border-t border-gray-100">
                    <a
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors mb-4 sm:mb-0 flex items-center"
                        href="{{ route('login') }}"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="px-6 py-2.5 rounded-lg text-sm font-medium bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200">
                        {{ __('Register') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </x-primary-button>
                </div>
            </form>
        </x-auth-card>
    </div>
</x-guest-layout>