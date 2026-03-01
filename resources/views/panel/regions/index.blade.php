@extends('layouts.panel')

@section('title', 'Bölgeler')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <!-- Header -->
    <div class="p-6 border-b flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Bölgeler</h1>
            <p class="text-sm text-gray-500 mt-1">Kurye ve vardiya atamalarında kullanılacak bölgeleri yönetin</p>
        </div>
        <a href="{{ route('panel.regions.create') }}" 
           class="px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Yeni Bölge
        </a>
    </div>

    <!-- Search -->
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Bölge adı ara..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                Ara
            </button>
            @if(request('search'))
                <a href="{{ route('panel.regions.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">
                    Temizle
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İl</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bölge Adı</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye Sayısı</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($regions as $region)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $region->city ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded" style="background-color: {{ $region->color }}"></div>
                                <div>
                                    <p class="font-medium">{{ $region->name }}</p>
                                    @if($region->description)
                                        <p class="text-xs text-gray-500">{{ Str::limit($region->description, 50) }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm">{{ $region->courier_count }} kurye</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($region->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Pasif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('panel.regions.edit', $region) }}" 
                                   class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Düzenle">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('panel.regions.destroy', $region) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Bu bölgeyi silmek istediğinize emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg" title="Sil">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <p>Henüz bölge oluşturulmamış</p>
                            <a href="{{ route('panel.regions.create') }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                İlk bölgeyi oluşturun
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($regions->hasPages())
        <div class="p-4 border-t">
            {{ $regions->links() }}
        </div>
    @endif
</div>
@endsection
