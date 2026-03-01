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
        <!-- Sidebar: flex column, menü ortada kaydırılabilir, alt kısım sabit -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-black text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-200 z-30 flex flex-col h-screen" id="sidebar">
            <div class="flex-shrink-0 flex items-center justify-center h-20 px-6 border-b border-gray-800 relative">
                <img src="/images/logo.png" alt="Papyon" style="height: 48px;" class="w-auto">
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white absolute right-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <nav class="flex-1 min-h-0 overflow-y-auto mt-6 px-3 pb-4">
                <!-- Genel -->
                <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Genel</p>
                <a href="{{ route('panel.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.dashboard') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('panel.shift-overview') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.shift-overview') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    Canlı Operasyon
                </a>

                <!-- Vardiya & Planlama -->
                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vardiya & Planlama</p>
                <div class="mb-1">
                    <button onclick="toggleSubmenu('shift-menu')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('panel.schedule.*') || request()->routeIs('panel.shifts.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Vardiya
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" id="shift-menu-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="shift-menu" class="submenu {{ request()->routeIs('panel.schedule.*') || request()->routeIs('panel.shifts.*') ? '' : 'hidden' }} ml-4 mt-1 space-y-1">
                        <a href="{{ route('panel.schedule.calendar') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.schedule.calendar') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Vardiya Planlama
                        </a>
                        <a href="{{ route('panel.shifts.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.shifts.index') || request()->routeIs('panel.shifts.show') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Vardiya Raporları
                        </a>
                        <a href="{{ route('panel.shifts.no-show') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.shifts.no-show') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Vardiyaya Girmeyenler
                        </a>
                    </div>
                </div>
                <div class="mb-1">
                    <button onclick="toggleSubmenu('regions-menu')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('panel.regions.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Bölgeler
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" id="regions-menu-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="regions-menu" class="submenu {{ request()->routeIs('panel.regions.*') ? '' : 'hidden' }} ml-4 mt-1 space-y-1">
                        <a href="{{ route('panel.regions.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.regions.index') || request()->routeIs('panel.regions.create') || request()->routeIs('panel.regions.show') || request()->routeIs('panel.regions.edit') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Bölge Listesi
                        </a>
                        <a href="{{ route('panel.regions.report') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.regions.report') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Bölge Raporu
                        </a>
                    </div>
                </div>

                <!-- Ekip -->
                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ekip</p>
                <div class="mb-1">
                    <div class="flex items-center rounded-lg {{ request()->routeIs('panel.couriers.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                        <a href="{{ route('panel.couriers.index', ['status' => 'active']) }}" class="flex-1 flex items-center px-4 py-3 rounded-l-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Kuryeler
                        </a>
                        <button type="button" onclick="toggleSubmenu('couriers-menu')" class="p-3 rounded-r-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" id="couriers-menu-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="couriers-menu" class="submenu {{ request()->routeIs('panel.couriers.*') ? '' : 'hidden' }} ml-4 mt-1 space-y-1">
                        <a href="{{ route('panel.couriers.index', ['status' => 'active']) }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.couriers.index') && request('status') !== 'inactive' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Aktif Kuryeler
                        </a>
                        <a href="{{ route('panel.couriers.index', ['status' => 'inactive']) }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request('status') === 'inactive' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Pasif Kuryeler
                        </a>
                    </div>
                </div>
                @can('manage-users')
                <a href="{{ route('panel.users.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.users.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Kullanıcılar
                </a>
                @endcan

                <!-- Masraf & Hakediş -->
                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Masraf & Hakediş</p>
                <div class="mb-1">
                    <button onclick="toggleSubmenu('expenses-menu')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('panel.expenses.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5 5l6-6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Masraf Yönetimi
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" id="expenses-menu-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="expenses-menu" class="submenu {{ request()->routeIs('panel.expenses.*') ? '' : 'hidden' }} ml-4 mt-1 space-y-1">
                        <a href="{{ route('panel.expenses.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.expenses.index') && !request()->routeIs('panel.expenses.history') && !request()->routeIs('panel.expenses.show') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Masraf Talepleri
                        </a>
                        <a href="{{ route('panel.expenses.history') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.expenses.history') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Geçmiş Masraf Talepleri
                        </a>
                    </div>
                </div>
                @can('manage-users')
                <div class="mb-1">
                    <button onclick="toggleSubmenu('settlement-menu')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg {{ request()->routeIs('panel.settlement.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hakediş
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" id="settlement-menu-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="settlement-menu" class="submenu {{ request()->routeIs('panel.settlement.*') ? '' : 'hidden' }} ml-4 mt-1 space-y-1">
                        <a href="{{ route('panel.settlement.calculation') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settlement.calculation') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Hakediş Hesaplama
                        </a>
                        <a href="{{ route('panel.settlement.settings') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settlement.settings') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Ayarlar
                        </a>
                        <a href="{{ route('panel.settlement.photo-review') }}" 
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settlement.photo-review') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Vardiya Uyumluluk İncelemesi
                        </a>
                        <a href="{{ route('panel.settlement.photo-compliance-report') }}"
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settlement.photo-compliance-report') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Vardiya Uyumluluk Raporu
                        </a>
                        <a href="{{ route('panel.settlement.deductions.index') }}"
                           class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('panel.settlement.deductions.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Kesintiler
                        </a>
                        <a href="{{ route('panel.settlement.calculation') }}?open_extra_bonus=1"
                           class="flex items-center px-4 py-2 rounded-lg text-sm text-gray-400 hover:bg-gray-800 hover:text-white">
                            Ekstra Prim Ekle
                        </a>
                    </div>
                </div>
                @endcan

                <!-- Raporlar -->
                @can('view-reports')
                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Raporlar</p>
                <a href="{{ route('panel.shifts.reports') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.shifts.reports') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Raporlar
                </a>
                @endcan

                <!-- Sistem -->
                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem</p>
                <a href="{{ route('panel.media-files.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg mb-1 {{ request()->routeIs('panel.media-files.*') ? 'bg-white text-black' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Ortam Dosyaları
                </a>
            </nav>
            
            <!-- User Info (sabit alt alan, menü bunun üstüne taşmaz) -->
            <div class="flex-shrink-0 p-4 border-t border-gray-800 bg-black">
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
        
        function toggleSubmenu(menuId) {
            const menu = document.getElementById(menuId);
            const arrow = document.getElementById(menuId + '-arrow');
            
            menu.classList.toggle('hidden');
            
            if (arrow) {
                if (menu.classList.contains('hidden')) {
                    arrow.style.transform = '';
                } else {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
        
        // Sayfa yüklendiğinde açık menülerin oklarını ayarla
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.submenu:not(.hidden)').forEach(menu => {
                const arrow = document.getElementById(menu.id + '-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
