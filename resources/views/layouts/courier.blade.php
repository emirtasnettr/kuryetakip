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
    <link rel="apple-touch-icon" sizes="152x152" href="/images/app-icon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/app-icon.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/images/app-icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kurye Takip') - Papyon</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f5f5',
                            100: '#e5e5e5',
                            200: '#d4d4d4',
                            300: '#a3a3a3',
                            400: '#737373',
                            500: '#525252',
                            600: '#404040',
                            700: '#262626',
                            800: '#171717',
                            900: '#0a0a0a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior: none;
        }
        
        .safe-area-top {
            padding-top: env(safe-area-inset-top);
        }
        
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        .spinner {
            border: 3px solid #e5e5e5;
            border-top: 3px solid #000000;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen safe-area-top safe-area-bottom">
    
    <!-- Header -->
    @hasSection('header')
        @yield('header')
    @else
        <header class="bg-black text-white sticky top-0 z-50 shadow-lg">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    @if(View::hasSection('back_url'))
                        <a href="@yield('back_url')" class="p-1 -ml-1 rounded-full hover:bg-gray-800 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <h1 class="text-lg font-semibold">@yield('title')</h1>
                    @endif
                </div>
                @if(!View::hasSection('back_url'))
                <div class="flex-1 flex justify-center">
                    <img src="/images/logo.png" alt="Papyon" class="w-auto" style="height: 42px;">
                </div>
                @endif
                @auth
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('courier.profile') }}" class="p-2 rounded-full hover:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    </div>
                @endauth
            </div>
        </header>
    @endif
    
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mx-4 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mx-4 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
    
    <!-- Main Content -->
    <main class="pb-20">
        @yield('content')
    </main>
    
    <!-- Bottom Navigation (Auth only) -->
    @auth
        @if(auth()->user()->isCourier())
            <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-bottom z-50">
                <div class="grid grid-cols-4 h-16">
                    <a href="{{ route('courier.home') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('courier.home') ? 'text-black' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-xs mt-1">Ana Sayfa</span>
                    </a>
                    <a href="{{ route('courier.shifts') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('courier.shifts') ? 'text-black' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="text-xs mt-1">Vardiyalar</span>
                    </a>
                    <a href="{{ route('courier.profile') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('courier.profile') ? 'text-black' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-xs mt-1">Profil</span>
                    </a>
                    <form action="{{ route('courier.logout') }}" method="POST" class="flex flex-col items-center justify-center">
                        @csrf
                        <button type="submit" class="flex flex-col items-center justify-center text-gray-400 w-full h-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-xs mt-1">Çıkış</span>
                        </button>
                    </form>
                </div>
            </nav>
        @endif
    @endauth
    
    <!-- PWA Install Prompt -->
    <div id="pwa-install-prompt" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl">
            <div class="w-20 h-20 bg-black rounded-2xl flex items-center justify-center mx-auto mb-4">
                <img src="/images/logo.png" alt="Papyon" class="h-12 w-auto">
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Uygulamayı Yükle</h3>
            <p class="text-gray-500 text-sm mb-6">Papyon Kurye'yi ana ekranınıza ekleyin ve uygulama gibi kullanın.</p>
            
            <div class="space-y-3">
                <button id="pwa-install-btn" class="w-full bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                    Ana Ekrana Ekle
                </button>
                <button id="pwa-dismiss-btn" class="w-full text-gray-500 py-2 text-sm hover:text-gray-700">
                    Daha Sonra
                </button>
            </div>
        </div>
    </div>

    <!-- Service Worker & PWA Script -->
    <script>
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered'))
                    .catch(err => console.log('SW registration failed'));
            });
        }

        // PWA Install Prompt
        let deferredPrompt;
        const installPrompt = document.getElementById('pwa-install-prompt');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Check if already dismissed recently
            const dismissed = localStorage.getItem('pwa-dismissed');
            if (dismissed) {
                const dismissedTime = parseInt(dismissed);
                const now = Date.now();
                // Show again after 24 hours
                if (now - dismissedTime < 24 * 60 * 60 * 1000) {
                    return;
                }
            }
            
            // Show install prompt after 2 seconds
            setTimeout(() => {
                installPrompt.classList.remove('hidden');
                installPrompt.classList.add('flex');
            }, 2000);
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('User choice:', outcome);
                    deferredPrompt = null;
                }
                installPrompt.classList.add('hidden');
                installPrompt.classList.remove('flex');
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                localStorage.setItem('pwa-dismissed', Date.now().toString());
                installPrompt.classList.add('hidden');
                installPrompt.classList.remove('flex');
            });
        }

        // Check if app is installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed');
            installPrompt.classList.add('hidden');
            installPrompt.classList.remove('flex');
            deferredPrompt = null;
        });
    </script>

    @stack('scripts')
</body>
</html>
