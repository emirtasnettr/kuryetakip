<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/images/app-icon.png">
    <link rel="shortcut icon" type="image/png" href="/images/app-icon.png">
    <title>@yield('title', 'Dashboard') - Papyon</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    
    <div class="flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-black text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-200 z-30" id="sidebar">
            <div class="flex items-center justify-center h-20 px-6 border-b border-gray-800 relative">
                <img src="/images/logo.png" alt="Papyon" style="height: 48px;" class="w-auto">
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white absolute right-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <nav class="mt-6 px-3">
                <a href="{{ route('panel.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.dashboard') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="{{ route('panel.shifts.active') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.shifts.active') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Aktif Vardiyalar
                </a>
                
                <a href="{{ route('panel.shifts.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.shifts.index') || request()->routeIs('panel.shifts.show') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Tüm Vardiyalar
                </a>
                
                <a href="{{ route('panel.couriers.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.couriers.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Kuryeler
                </a>
                
                @can('view-reports')
                <a href="{{ route('panel.shifts.reports') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.shifts.reports') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Raporlar
                </a>
                @endcan
                
                @can('manage-users')
                <a href="{{ route('panel.users.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.users.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Kullanıcılar
                </a>
                @endcan
            </nav>
            
            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white text-black rounded-full flex items-center justify-center">
                        <span class="font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->role->display_name }}</p>
                    </div>
                    <form action="{{ route('panel.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-screen">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 sticky top-0 z-10">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-800 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="text-sm text-gray-500">
                    {{ now()->translatedFormat('d F Y, H:i') }}
                </div>
            </header>
            
            <!-- Flash Messages -->
            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
            
            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
    
    @stack('scripts')
</body>
</html>
