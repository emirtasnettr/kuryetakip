<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Papyon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #333333 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md relative z-10">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="Papyon" class="w-auto mx-auto" style="height: 88px;">
        </div>
        
        <!-- Login Card -->
        <div class="glass-card rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Hoş Geldiniz</h2>
                <p class="text-gray-500 text-sm mt-1">Devam etmek için giriş yapın</p>
            </div>
            
            <!-- Error Messages -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif
            
            <!-- Form -->
            <form method="POST" action="{{ route('panel.login.submit') }}" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta Adresi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl input-focus focus:outline-none focus:border-black transition-all text-gray-800 placeholder-gray-400"
                            placeholder="ornek@sirket.com"
                        >
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl input-focus focus:outline-none focus:border-black transition-all text-gray-800 placeholder-gray-400"
                            placeholder="••••••••"
                        >
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-black bg-gray-100 border-gray-300 rounded focus:ring-black focus:ring-2">
                        <span class="ml-2 text-sm text-gray-600">Beni hatırla</span>
                    </label>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full bg-black text-white py-3.5 rounded-xl font-semibold hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    Giriş Yap
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © {{ date('Y') }} Papyon. Tüm hakları saklıdır.
            </p>
        </div>
    </div>
    
</body>
</html>
