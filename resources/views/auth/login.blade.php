<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-blue-200 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Enhanced Cloud-like background shapes -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Large floating shapes -->
            <div class="absolute top-1/6 left-1/6 w-48 h-24 bg-white/40 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute top-1/4 right-1/5 w-36 h-18 bg-white/30 rounded-full blur-2xl"></div>
            <div class="absolute bottom-1/4 left-1/4 w-56 h-28 bg-white/35 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/6 right-1/6 w-40 h-20 bg-white/25 rounded-full blur-2xl"></div>
            <div class="absolute top-1/2 left-1/2 w-32 h-16 bg-white/20 rounded-full blur-xl transform -translate-x-1/2 -translate-y-1/2"></div>

            <!-- Medium shapes -->
            <div class="absolute top-1/3 left-1/2 w-24 h-12 bg-white/25 rounded-full blur-lg"></div>
            <div class="absolute bottom-1/3 right-1/3 w-28 h-14 bg-white/20 rounded-full blur-lg"></div>
            <div class="absolute top-2/3 left-1/3 w-20 h-10 bg-white/30 rounded-full blur-lg"></div>

            <!-- Subtle connecting lines with animation -->
            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M10,80 Q30,60 50,70 T90,60" stroke="rgba(255,255,255,0.4)" stroke-width="0.3" fill="none" opacity="0.6">
                    <animate attributeName="opacity" values="0.3;0.7;0.3" dur="4s" repeatCount="indefinite"/>
                </path>
                <path d="M20,90 Q40,70 60,80 T95,70" stroke="rgba(255,255,255,0.3)" stroke-width="0.2" fill="none" opacity="0.4">
                    <animate attributeName="opacity" values="0.2;0.6;0.2" dur="6s" repeatCount="indefinite"/>
                </path>
                <path d="M5,50 Q25,30 45,40 T85,30" stroke="rgba(255,255,255,0.2)" stroke-width="0.2" fill="none" opacity="0.3">
                    <animate attributeName="opacity" values="0.1;0.5;0.1" dur="8s" repeatCount="indefinite"/>
                </path>
            </svg>

            <!-- Floating particles -->
            <div class="absolute top-1/5 left-1/5 w-2 h-2 bg-white/60 rounded-full animate-bounce"></div>
            <div class="absolute top-2/5 right-1/4 w-1.5 h-1.5 bg-white/50 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-1/5 left-1/3 w-1 h-1 bg-white/40 rounded-full animate-bounce" style="animation-delay: 2s;"></div>
        </div>

        <div class="relative max-w-md w-full space-y-8">

            <!-- Session Status -->
            @if (session('status'))
                <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-green-300 font-medium">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Login Form Card with Enhanced Glassmorphism -->
            <div class="bg-white/20 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 p-8 relative overflow-hidden">
                <!-- Glass reflection effect -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-white/40 to-transparent"></div>
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>

                <!-- Inner glow effect -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-white/5 rounded-3xl"></div>
                <!-- Card Header -->
                <div class="text-center mb-8 relative z-10">
                    <div class="mx-auto h-12 w-12 bg-white/30 backdrop-blur-sm rounded-lg flex items-center justify-center mb-4 border border-white/40 shadow-lg">
                        <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2 drop-shadow-sm">Sign in with email</h2>
                    <p class="text-sm text-gray-600 drop-shadow-sm">Make a new doc to bring your words, data, and teams together. For free</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6 relative z-10">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input id="email"
                                   class="block w-full pl-10 pr-3 py-3 bg-white/30 backdrop-blur-sm border border-white/40 rounded-xl text-gray-800 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/60 transition-all duration-300 shadow-lg"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="Email" />
                        </div>
                        @error('email')
                            <p class="text-red-500 text-sm font-medium flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input id="password"
                                   class="block w-full pl-10 pr-12 py-3 bg-white/30 backdrop-blur-sm border border-white/40 rounded-xl text-gray-800 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/60 transition-all duration-300 shadow-lg"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Password" />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center z-10">
                                <button type="button" class="text-gray-600 hover:text-gray-800 transition-colors duration-200" onclick="togglePassword()">
                                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-sm font-medium flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Get Started Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 px-6 bg-gray-800/90 backdrop-blur-sm text-white font-semibold rounded-xl hover:bg-gray-900/90 focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-2 transition-all duration-300 shadow-xl border border-gray-700/50">
                            Get Started
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const paths = eyeIcon.querySelectorAll('path');

            if (passwordInput.type === 'password') {
                // Show password - change to eye with slash
                passwordInput.type = 'text';
                paths[0].setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21');
                paths[1].setAttribute('d', 'M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21');
            } else {
                // Hide password - change to normal eye
                passwordInput.type = 'password';
                paths[0].setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
                paths[1].setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
            }
        }
    </script>
</x-guest-layout>
