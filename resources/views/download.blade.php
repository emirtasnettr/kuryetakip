<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#000000">
    <title>Papyon Kurye - Uygulamayı İndir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(180deg, #000000 0%, #171717 100%);
        }
        .pulse-btn {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-white/5 rounded-full blur-3xl -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-white/5 rounded-full blur-3xl translate-y-1/2"></div>
    </div>
    
    <div class="relative min-h-screen flex flex-col items-center justify-center px-6 py-12">
        
        <!-- Logo -->
        <div class="float mb-8">
            <img src="/images/logo.png" alt="Papyon" class="h-24 w-auto">
        </div>
        
        <!-- App Icon Mockup -->
        <div class="mb-8">
            <div class="w-28 h-28 bg-white rounded-3xl shadow-2xl flex items-center justify-center p-4">
                <img src="/images/logo.png" alt="Papyon" class="w-full h-full object-contain" style="filter: invert(1);">
            </div>
        </div>
        
        <!-- Title -->
        <h1 class="text-3xl font-bold text-white mb-2 text-center">Papyon Kurye</h1>
        <p class="text-gray-400 text-center mb-8 max-w-xs">Vardiya takip uygulamasını indirin ve hemen kullanmaya başlayın.</p>
        
        <!-- Features -->
        <div class="grid grid-cols-3 gap-4 mb-10 max-w-sm w-full">
            <div class="text-center">
                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-white/60 text-xs">GPS Konum</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-white/60 text-xs">Fotoğraf</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-white/60 text-xs">Vardiya Takip</p>
            </div>
        </div>
        
        <!-- Download Button -->
        <a href="/apk/papyon.apk" download="PapyonKurye.apk" 
           class="pulse-btn w-full max-w-xs bg-white text-black py-4 px-8 rounded-2xl font-bold text-lg text-center flex items-center justify-center space-x-3 hover:bg-gray-100 transition-colors shadow-2xl">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Android İndir</span>
        </a>
        
        <!-- Version Info -->
        <p class="text-white/30 text-sm mt-4">Versiyon 1.0 • Android 7.0+</p>
        
        <!-- Instructions -->
        <div class="mt-10 bg-white/5 rounded-2xl p-6 max-w-sm w-full border border-white/10">
            <h3 class="text-white font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Kurulum Adımları
            </h3>
            <ol class="text-white/60 text-sm space-y-3">
                <li class="flex items-start">
                    <span class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center text-xs text-white mr-3 flex-shrink-0">1</span>
                    <span>Yukarıdaki butona tıklayarak APK dosyasını indirin</span>
                </li>
                <li class="flex items-start">
                    <span class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center text-xs text-white mr-3 flex-shrink-0">2</span>
                    <span>İndirilen dosyaya tıklayın ve "Yükle" seçeneğini seçin</span>
                </li>
                <li class="flex items-start">
                    <span class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center text-xs text-white mr-3 flex-shrink-0">3</span>
                    <span>"Bilinmeyen kaynaklara izin ver" uyarısı çıkarsa izin verin</span>
                </li>
                <li class="flex items-start">
                    <span class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center text-xs text-white mr-3 flex-shrink-0">4</span>
                    <span>Kurulum tamamlandıktan sonra uygulamayı açın</span>
                </li>
            </ol>
        </div>
        
        <!-- Footer -->
        <div class="mt-10 text-center">
            <p class="text-white/30 text-xs">© {{ date('Y') }} Papyon</p>
        </div>
        
    </div>
    
</body>
</html>
