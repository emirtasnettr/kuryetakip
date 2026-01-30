<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Papyon">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/images/app-icon.png">
    <link rel="shortcut icon" type="image/png" href="/images/app-icon.png">
    <link rel="apple-touch-icon" href="/images/app-icon.png">
    <title>Giriş - Papyon Kurye</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(180deg, #000000 0%, #171717 100%);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl -translate-y-1/2"></div>
    </div>
    
    <div class="relative min-h-screen flex flex-col justify-center px-6 py-12">
        
        <!-- Logo -->
        <div class="text-center mb-10">
            <img src="/images/logo.png" alt="Papyon" class="h-16 w-auto mx-auto mb-6">
            <div class="w-16 h-1 bg-white/20 mx-auto rounded-full"></div>
        </div>
        
        <!-- Login Card -->
        <div class="w-full max-w-sm mx-auto">
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-6 border border-white/10">
                
                <!-- Error Messages -->
                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-xl mb-6 text-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('courier.login.submit') }}" class="space-y-5">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-white/70 mb-2">E-posta</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                autocomplete="email"
                                class="w-full pl-12 pr-4 py-3.5 bg-white/10 border border-white/10 rounded-xl input-focus focus:outline-none focus:border-white/30 text-white placeholder-white/40 transition-all"
                                placeholder="ornek@email.com"
                            >
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-white/70 mb-2">Şifre</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                class="w-full pl-12 pr-4 py-3.5 bg-white/10 border border-white/10 rounded-xl input-focus focus:outline-none focus:border-white/30 text-white placeholder-white/40 transition-all"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            class="w-4 h-4 bg-white/10 border-white/20 rounded focus:ring-white/30 focus:ring-2 text-white"
                        >
                        <label for="remember" class="ml-2 text-sm text-white/60">Beni hatırla</label>
                    </div>
                    
                    <!-- Submit -->
                    <button 
                        type="submit"
                        class="w-full bg-white text-black py-3.5 rounded-xl font-semibold hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-black transition-all transform active:scale-[0.98]"
                    >
                        Giriş Yap
                    </button>
                </form>
            </div>
            
            <!-- Info -->
            <p class="text-center text-white/40 text-sm mt-6">
                Giriş bilgilerinizi operasyon ekibinden alabilirsiniz.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-auto pt-8">
            <p class="text-white/30 text-xs">
                © {{ date('Y') }} Papyon
            </p>
        </div>
        
    </div>
    
</body>
</html>
