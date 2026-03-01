@extends('layouts.panel')

@section('title', 'Vardiya Planlama')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    /* Takvim stilleri */
    .fc {
        font-family: inherit;
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .fc .fc-button-primary {
        background-color: #000;
        border-color: #000;
    }
    .fc .fc-button-primary:hover {
        background-color: #333;
        border-color: #333;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #000;
        border-color: #000;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.75rem;
    }
    .fc-event-title {
        font-weight: 500;
    }
    .fc-timegrid-slot {
        height: 40px !important;
    }
    .fc-timegrid-event {
        border-radius: 4px;
    }
    
    /* Kurye kartları */
    .courier-card {
        cursor: grab;
        transition: all 0.2s;
    }
    .courier-card:active {
        cursor: grabbing;
    }
    .courier-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .courier-card.dragging {
        opacity: 0.5;
    }
    .courier-card.assigned {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .courier-card.busy {
        background-color: #fef3c7;
    }

    /* Modal stilleri */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    /* Renk seçici */
    .color-option {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    .color-option:hover {
        transform: scale(1.1);
    }
    .color-option.selected {
        border-color: #000;
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #000;
    }

    /* Atama listesi */
    .assignment-item {
        transition: all 0.2s;
    }
    .assignment-item:hover {
        background-color: #f3f4f6;
    }

    /* Sürükle bırak hedef alanı */
    .drop-zone {
        min-height: 100px;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .drop-zone.drag-over {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }

    /* Arama özellikli seçici */
    .searchable-select {
        position: relative;
    }
    .searchable-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        min-width: 160px;
        font-size: 14px;
    }
    .searchable-select-trigger:hover {
        border-color: #9ca3af;
    }
    .searchable-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 4px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        z-index: 100;
        max-height: 300px;
        display: none;
    }
    .searchable-select-dropdown.open {
        display: block;
    }
    .searchable-select-search {
        padding: 8px;
        border-bottom: 1px solid #e5e7eb;
    }
    .searchable-select-search input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    .searchable-select-search input:focus {
        outline: none;
        border-color: #000;
    }
    .searchable-select-options {
        max-height: 220px;
        overflow-y: auto;
    }
    .searchable-select-option {
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .searchable-select-option:hover {
        background-color: #f3f4f6;
    }
    .searchable-select-option.selected {
        background-color: #f0fdf4;
    }
    .searchable-select-option.hidden {
        display: none;
    }

    /* Tag/Badge stili seçili öğeler için */
    .selected-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 8px;
    }
    .selected-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        background: #e5e7eb;
        border-radius: 4px;
        font-size: 12px;
    }
    .selected-tag button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #9ca3af;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 10px;
        line-height: 1;
    }
    .selected-tag button:hover {
        background: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="flex gap-6">
    <!-- Sol Panel: Takvim -->
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <!-- Toolbar -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('panel.schedule.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    Liste görünümü
                </a>
                <!-- Bölge Filtresi -->
                <select id="region-filter" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-black focus:border-black">
                    <option value="">Tüm Bölgeler</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" data-color="{{ $region->color }}">
                            {{ $region->city ? $region->city . ' - ' : '' }}{{ $region->name }}
                        </option>
                    @endforeach
                </select>
                
                @if($regions->isEmpty())
                    <a href="{{ route('panel.regions.create') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        + İlk bölgeyi oluştur
                    </a>
                @endif
                
                <button id="btn-today" class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Bugün
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button id="btn-new-shift" class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Yeni Vardiya
                </button>
                <button id="btn-bulk-create" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    Toplu Oluştur
                </button>
                <button id="btn-excel-upload" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Excel ile Yükle
                </button>
            </div>
        </div>

        <!-- Takvim -->
        <div id="calendar"></div>
    </div>

    <!-- Sağ Panel: Kuryeler -->
    <div class="w-80 bg-white rounded-xl shadow-sm p-6 flex flex-col max-h-[calc(100vh-180px)]">
        <!-- Seçili Vardiya Bilgisi -->
        <div id="selected-shift-info" class="hidden mb-4 pb-4 border-b">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-sm" id="selected-shift-title">-</h3>
                <button onclick="openDetailModal(currentShift?.id)" class="text-xs text-blue-600 hover:text-blue-800">
                    Detay
                </button>
            </div>
            <div class="text-xs text-gray-500 space-y-1">
                <p id="selected-shift-district"></p>
                <p id="selected-shift-time"></p>
                <p id="selected-shift-capacity"></p>
            </div>
        </div>
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Kuryeler</h3>
            <span id="courier-count" class="text-xs text-gray-500"></span>
        </div>
        
        <!-- Kurye Arama -->
        <div class="relative mb-4">
            <input type="text" id="courier-search" placeholder="Kurye ara..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Kurye Listesi -->
        <div id="courier-list" class="flex-1 overflow-y-auto space-y-2">
            <p class="text-gray-500 text-sm text-center py-4">Takvimden bir vardiya seçin</p>
        </div>
    </div>
</div>

<!-- Yeni Vardiya Modal -->
<div id="shift-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeShiftModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 id="shift-modal-title" class="text-xl font-semibold">Yeni Vardiya</h2>
                <button onclick="closeShiftModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="shift-form">
                <input type="hidden" id="shift-id" name="id">
                
                <div class="space-y-4">
                    <!-- Bölge Seçimi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bölge *</label>
                        <select id="shift-region" name="region_id" required
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                            <option value="">Bölge seçin</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                        @if($regions->isEmpty())
                            <p class="text-xs text-yellow-600 mt-1">
                                <a href="{{ route('panel.regions.create') }}" class="underline">Önce bir bölge oluşturun</a>
                            </p>
                        @endif
                    </div>

                    <!-- Tarih -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                        <input type="date" id="shift-date" name="shift_date" required
                               class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                    </div>

                    <!-- Saat Aralığı -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç</label>
                            <input type="time" id="shift-start-time" name="start_time" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş</label>
                            <input type="time" id="shift-end-time" name="end_time" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <!-- Kurye Sayısı -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gereken Kurye Sayısı</label>
                        <input type="number" id="shift-couriers" name="required_couriers" min="1" max="50" value="1" required
                               class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                    </div>

                    <!-- Başlık (Opsiyonel) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Başlık (Opsiyonel)</label>
                        <input type="text" id="shift-title" name="title" placeholder="Örn: Öğle Vardiyası"
                               class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                    </div>

                    <!-- Renk -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                        <div class="flex gap-2" id="color-picker">
                            <div class="color-option selected" data-color="#3B82F6" style="background-color: #3B82F6"></div>
                            <div class="color-option" data-color="#10B981" style="background-color: #10B981"></div>
                            <div class="color-option" data-color="#F59E0B" style="background-color: #F59E0B"></div>
                            <div class="color-option" data-color="#EF4444" style="background-color: #EF4444"></div>
                            <div class="color-option" data-color="#8B5CF6" style="background-color: #8B5CF6"></div>
                            <div class="color-option" data-color="#EC4899" style="background-color: #EC4899"></div>
                            <div class="color-option" data-color="#6366F1" style="background-color: #6366F1"></div>
                            <div class="color-option" data-color="#14B8A6" style="background-color: #14B8A6"></div>
                        </div>
                        <input type="hidden" id="shift-color" name="color" value="#3B82F6">
                    </div>

                    <!-- Notlar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
                        <textarea id="shift-notes" name="notes" rows="2"
                                  class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                                  placeholder="Varsa ek notlar..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeShiftModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vardiya Detay Modal -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeDetailModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 id="detail-title" class="text-xl font-semibold">Vardiya Detayı</h2>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="detail-content">
                <!-- İçerik JavaScript ile doldurulacak -->
            </div>
        </div>
    </div>
</div>

<!-- Toplu Oluşturma Modal -->
<div id="bulk-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeBulkModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold">Toplu Vardiya Oluştur</h2>
                <button onclick="closeBulkModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="bulk-form">
                <div class="space-y-4">
                    <!-- Bölge Seçimi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bölge *</label>
                        <select id="bulk-region" name="region_id" required
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                            <option value="">Bölge seçin</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tarih Aralığı -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
                            <input type="date" id="bulk-start-date" name="start_date" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
                            <input type="date" id="bulk-end-date" name="end_date" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <!-- Saat Aralığı -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Saati</label>
                            <input type="time" id="bulk-start-time" name="start_time" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Saati</label>
                            <input type="time" id="bulk-end-time" name="end_time" required
                                   class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <!-- Kurye Sayısı -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gereken Kurye Sayısı</label>
                        <input type="number" id="bulk-couriers" name="required_couriers" min="1" max="50" value="1" required
                               class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black">
                    </div>

                    <!-- Renk -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                        <div class="flex gap-2" id="bulk-color-picker">
                            <div class="color-option selected" data-color="#3B82F6" style="background-color: #3B82F6"></div>
                            <div class="color-option" data-color="#10B981" style="background-color: #10B981"></div>
                            <div class="color-option" data-color="#F59E0B" style="background-color: #F59E0B"></div>
                            <div class="color-option" data-color="#EF4444" style="background-color: #EF4444"></div>
                            <div class="color-option" data-color="#8B5CF6" style="background-color: #8B5CF6"></div>
                            <div class="color-option" data-color="#EC4899" style="background-color: #EC4899"></div>
                        </div>
                        <input type="hidden" id="bulk-color" name="color" value="#3B82F6">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeBulkModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800">
                        Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Excel ile Yükle Modal -->
<div id="excel-upload-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeExcelUploadModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold">Excel ile Vardiya Yükle</h2>
                <button type="button" onclick="closeExcelUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Sütunlar: Tarih, Kurye T.C, Bölge, Başlangıç Saati, Bitiş Saati. Kurye T.C kimlik numarası ile eşleştirilir.</p>
            <div class="flex flex-col gap-3">
                <a href="{{ route('panel.schedule.shift-template') }}" class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Şablon indir
                </a>
                <form action="{{ route('panel.schedule.shift-upload') }}" method="post" enctype="multipart/form-data" id="excel-upload-form">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <button type="submit" class="mt-3 w-full px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800">
                        Yükle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/tr.global.min.js"></script>
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // State
    let calendar;
    let currentShift = null;
    let selectedRegionId = ''; // Bölge filtresi
    
    // Çift tıklama kontrolü için
    let lastClickedEventId = null;
    let lastClickTime = 0;
    let singleClickTimer = null;

    // FullCalendar Başlat
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'tr',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '06:00:00',
            slotMaxTime: '24:00:00',
            slotDuration: '01:00:00',
            allDaySlot: false,
            nowIndicator: true,
            editable: true,
            droppable: true,
            eventResizableFromStart: true,
            selectable: true,
            selectMirror: true,
            
            // Event kaynağı
            events: function(info, successCallback, failureCallback) {
                fetchEvents(info.startStr, info.endStr, successCallback);
            },
            
            // Takvimde boş alana tıklama - yeni vardiya
            select: function(info) {
                openShiftModal(null, info.startStr, info.endStr);
            },
            
            // Event'e tıklama - tek tık seçim, çift tık düzenleme
            eventClick: function(info) {
                const eventId = info.event.id;
                const now = Date.now();
                
                // Çift tıklama kontrolü (300ms içinde ikinci tık)
                if (lastClickedEventId === eventId && (now - lastClickTime) < 300) {
                    // Çift tık - düzenleme modalını aç
                    clearTimeout(singleClickTimer);
                    openShiftModal(eventId);
                    lastClickedEventId = null;
                    lastClickTime = 0;
                } else {
                    // Tek tık - seçim yap (300ms bekle çift tık kontrolü için)
                    lastClickedEventId = eventId;
                    lastClickTime = now;
                    
                    singleClickTimer = setTimeout(() => {
                        selectShift(info.event);
                        lastClickedEventId = null;
                    }, 300);
                }
            },
            
            // Event sürükleme (tarih/saat değiştirme)
            eventDrop: function(info) {
                moveShift(info.event.id, info.event.startStr, info.event.endStr);
            },
            
            // Event boyutlandırma
            eventResize: function(info) {
                moveShift(info.event.id, info.event.startStr, info.event.endStr);
            },
            
            // Event içeriği özelleştirme
            eventContent: function(arg) {
                const props = arg.event.extendedProps;
                const assigned = props.assigned_count || 0;
                const required = props.required_couriers || 0;
                const isFull = assigned >= required;
                
                return {
                    html: `
                        <div class="fc-event-main-frame p-1">
                            <div class="fc-event-title-container">
                                <div class="fc-event-title font-medium">${arg.event.title}</div>
                            </div>
                            <div class="text-xs mt-1 ${isFull ? 'text-green-200' : 'text-yellow-200'}">
                                ${assigned}/${required} kurye
                            </div>
                        </div>
                    `
                };
            },
            
            // Kurye sürükle-bırak
            drop: function(info) {
                const courierId = info.draggedEl.dataset.courierId;
                if (currentShift) {
                    assignCourier(currentShift.id, courierId);
                }
            }
        });
        
        calendar.render();
        
        // Bölge filtresi değiştiğinde
        document.getElementById('region-filter').addEventListener('change', function() {
            selectedRegionId = this.value;
            calendar.refetchEvents();
        });
        
        // Bugün butonu
        document.getElementById('btn-today').addEventListener('click', function() {
            calendar.today();
        });
        
        // Yeni vardiya butonu
        document.getElementById('btn-new-shift').addEventListener('click', function() {
            openShiftModal();
        });
        
        // Toplu oluştur butonu
        document.getElementById('btn-bulk-create').addEventListener('click', function() {
            openBulkModal();
        });

        // Excel ile yükle butonu
        document.getElementById('btn-excel-upload').addEventListener('click', function() {
            document.getElementById('excel-upload-modal').classList.remove('hidden');
        });

        // Session mesajları (yükleme sonrası yönlendirmede)
        @if(session('success'))
            showToast({!! json_encode(session('success')) !!}, 'success');
        @endif
        @if(session('error'))
            showToast({!! json_encode(session('error')) !!}, 'error');
        @endif
        @if(session('warning'))
            showToast({!! json_encode(session('warning')) !!}, 'error');
        @endif
        
        // Renk seçici
        setupColorPicker('color-picker', 'shift-color');
        setupColorPicker('bulk-color-picker', 'bulk-color');
        
        // Formlar
        document.getElementById('shift-form').addEventListener('submit', handleShiftSubmit);
        document.getElementById('bulk-form').addEventListener('submit', handleBulkSubmit);
        
        // Kurye arama
        document.getElementById('courier-search').addEventListener('input', function() {
            filterCouriers(this.value);
        });
        
        // Bugünün tarihini varsayılan olarak ayarla
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('shift-date').min = today;
        document.getElementById('bulk-start-date').min = today;
        document.getElementById('bulk-end-date').min = today;
    });
    
    // ==================== FİLTRE DROPDOWN FONKSİYONLARI ====================
    
    function toggleFilterDropdown(type) {
        const dropdown = document.getElementById(`${type}-dropdown`);
        const isOpen = dropdown.classList.contains('open');
        
        // Tüm dropdown'ları kapat
        document.querySelectorAll('.searchable-select-dropdown').forEach(d => d.classList.remove('open'));
        
        // Bu dropdown'ı aç/kapat
        if (!isOpen) {
            dropdown.classList.add('open');
            dropdown.querySelector('input')?.focus();
        }
    }
    
    function filterOptions(type, search) {
        const options = document.querySelectorAll(`#${type}-options .searchable-select-option`);
        const searchLower = search.toLowerCase();
        
        options.forEach(opt => {
            const text = opt.textContent.toLowerCase();
            opt.classList.toggle('hidden', !text.includes(searchLower));
        });
    }
    
    function selectFilterCity(city) {
        selectedCity = city;
        document.getElementById('filter-city-label').textContent = city || 'Tüm İller';
        document.getElementById('filter-city-dropdown').classList.remove('open');
        
        // Seçili işaretini güncelle
        document.querySelectorAll('#filter-city-options .searchable-select-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value === city);
        });
        
        // İlçe filtresini sıfırla
        selectedDistrictIds = [];
        updateFilterDistrictLabel();
        calendar.refetchEvents();
    }
    
    function updateFilterDistrictLabel() {
        const label = document.getElementById('filter-district-label');
        const countBadge = document.getElementById('filter-district-count');
        const clearBtn = document.getElementById('btn-clear-filter');
        
        if (selectedDistrictIds.length === 0) {
            label.textContent = 'Tüm İlçeler';
            countBadge.classList.add('hidden');
            clearBtn.classList.add('hidden');
        } else if (selectedDistrictIds.length === 1) {
            const district = filterSelectedDistricts.find(d => d.id === selectedDistrictIds[0]);
            label.textContent = district ? district.name : '1 İlçe';
            countBadge.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        } else {
            label.textContent = `${selectedDistrictIds.length} İlçe`;
            countBadge.textContent = selectedDistrictIds.length;
            countBadge.classList.remove('hidden');
            clearBtn.classList.remove('hidden');
        }
    }
    
    function clearDistrictFilter() {
        selectedDistrictIds = [];
        filterSelectedDistricts = [];
        updateFilterDistrictLabel();
        calendar.refetchEvents();
    }
    
    // ==================== FİLTRE BÖLGE SEÇİCİ MODAL ====================
    
    function openFilterDistrictSelector() {
        // Mevcut seçimleri koru
        filterSelectorCity = selectedCity || 'İstanbul';
        document.getElementById('filter-selector-city-search').value = filterSelectorCity;
        
        // Mevcut filtreleri modal'a yükle
        // filterSelectedDistricts zaten güncel olmalı
        
        loadDistrictsForFilterSelector(filterSelectorCity);
        updateFilterSelectorSelectedUI();
        
        document.getElementById('filter-district-modal').classList.remove('hidden');
    }
    
    function closeFilterDistrictSelector() {
        document.getElementById('filter-district-modal').classList.add('hidden');
    }
    
    function showFilterCityList() {
        document.getElementById('filter-selector-city-list').classList.remove('hidden');
    }
    
    function filterFilterCityList(search) {
        const list = document.getElementById('filter-selector-city-list');
        const items = list.querySelectorAll('div');
        const searchLower = search.toLowerCase();
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchLower) ? '' : 'none';
        });
        
        list.classList.remove('hidden');
    }
    
    function selectFilterSelectorCity(city) {
        filterSelectorCity = city;
        document.getElementById('filter-selector-city-search').value = city;
        document.getElementById('filter-selector-city-list').classList.add('hidden');
        loadDistrictsForFilterSelector(city);
    }
    
    async function loadDistrictsForFilterSelector(city) {
        const container = document.getElementById('filter-selector-districts-list');
        container.innerHTML = '<p class="text-gray-400 text-sm col-span-2 text-center py-4">Yükleniyor...</p>';
        
        const districts = await fetchDistricts(city);
        
        if (districts.length === 0) {
            container.innerHTML = '<p class="text-gray-400 text-sm col-span-2 text-center py-4">Bu ilde ilçe bulunamadı</p>';
            return;
        }
        
        container.innerHTML = districts.map(d => {
            const isSelected = filterSelectedDistricts.some(s => s.id === d.id);
            return `
                <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-gray-50 ${isSelected ? 'bg-green-50 border-green-300' : ''}" 
                       data-filter-district-id="${d.id}" data-filter-district-name="${d.name}">
                    <input type="checkbox" ${isSelected ? 'checked' : ''} 
                           onchange="toggleFilterDistrictSelection(${d.id}, '${d.name.replace(/'/g, "\\'")}', this.checked)"
                           class="rounded border-gray-300 text-black focus:ring-black">
                    <span class="text-sm">${d.name}</span>
                </label>
            `;
        }).join('');
    }
    
    function filterFilterDistrictList(search) {
        const container = document.getElementById('filter-selector-districts-list');
        const items = container.querySelectorAll('label');
        const searchLower = search.toLowerCase();
        
        items.forEach(item => {
            const name = item.dataset.filterDistrictName?.toLowerCase() || '';
            item.style.display = name.includes(searchLower) ? '' : 'none';
        });
    }
    
    function toggleFilterDistrictSelection(id, name, isChecked) {
        if (isChecked) {
            if (!filterSelectedDistricts.some(d => d.id === id)) {
                filterSelectedDistricts.push({ id, name });
            }
        } else {
            filterSelectedDistricts = filterSelectedDistricts.filter(d => d.id !== id);
        }
        
        updateFilterSelectorSelectedUI();
        
        // Checkbox'ın parent label'ını güncelle
        const label = document.querySelector(`label[data-filter-district-id="${id}"]`);
        if (label) {
            label.classList.toggle('bg-green-50', isChecked);
            label.classList.toggle('border-green-300', isChecked);
        }
    }
    
    function removeFilterSelectedDistrict(id) {
        filterSelectedDistricts = filterSelectedDistricts.filter(d => d.id !== id);
        updateFilterSelectorSelectedUI();
        
        // Checkbox'ı da güncelle
        const label = document.querySelector(`label[data-filter-district-id="${id}"]`);
        if (label) {
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
            label.classList.remove('bg-green-50', 'border-green-300');
        }
    }
    
    function clearAllFilterSelectedDistricts() {
        filterSelectedDistricts = [];
        updateFilterSelectorSelectedUI();
        
        // Tüm checkbox'ları temizle
        document.querySelectorAll('#filter-selector-districts-list input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
            cb.closest('label').classList.remove('bg-green-50', 'border-green-300');
        });
    }
    
    function updateFilterSelectorSelectedUI() {
        const container = document.getElementById('filter-selector-selected-container');
        const tagsContainer = document.getElementById('filter-selector-selected-tags');
        const countEl = document.getElementById('filter-selector-selected-count');
        
        countEl.textContent = filterSelectedDistricts.length;
        
        if (filterSelectedDistricts.length === 0) {
            container.classList.add('hidden');
            return;
        }
        
        container.classList.remove('hidden');
        tagsContainer.innerHTML = filterSelectedDistricts.map(d => `
            <span class="selected-tag">
                ${d.name}
                <button type="button" onclick="removeFilterSelectedDistrict(${d.id})">&times;</button>
            </span>
        `).join('');
    }
    
    function applyFilterDistrictSelection() {
        // Seçimleri ana filtreye uygula
        selectedDistrictIds = filterSelectedDistricts.map(d => d.id);
        updateFilterDistrictLabel();
        
        closeFilterDistrictSelector();
        calendar.refetchEvents();
    }
    
    function clearAndApplyFilter() {
        filterSelectedDistricts = [];
        selectedDistrictIds = [];
        updateFilterSelectorSelectedUI();
        updateFilterDistrictLabel();
        
        closeFilterDistrictSelector();
        calendar.refetchEvents();
    }
    
    // ==================== BÖLGE SEÇİCİ MODAL FONKSİYONLARI ====================
    
    let currentSelectorTarget = null; // 'shift' veya 'bulk'
    let selectorSelectedDistricts = []; // {id, name} array
    let currentSelectorCity = 'İstanbul';
    
    function openDistrictSelector(target) {
        currentSelectorTarget = target;
        
        // Mevcut seçimleri yükle
        const existingIds = document.getElementById(`${target}-district-ids`).value;
        if (existingIds) {
            // Mevcut seçimler varsa koru (bu daha sonra implement edilebilir)
        } else {
            selectorSelectedDistricts = [];
        }
        
        // Varsayılan il
        document.getElementById('selector-city-search').value = currentSelectorCity;
        loadDistrictsForSelector(currentSelectorCity);
        updateSelectorSelectedUI();
        
        document.getElementById('district-selector-modal').classList.remove('hidden');
    }
    
    function closeDistrictSelector() {
        document.getElementById('district-selector-modal').classList.add('hidden');
        currentSelectorTarget = null;
    }
    
    function showCityList() {
        document.getElementById('selector-city-list').classList.remove('hidden');
    }
    
    function filterCityList(search) {
        const list = document.getElementById('selector-city-list');
        const items = list.querySelectorAll('div');
        const searchLower = search.toLowerCase();
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchLower) ? '' : 'none';
        });
        
        list.classList.remove('hidden');
    }
    
    function selectSelectorCity(city) {
        currentSelectorCity = city;
        document.getElementById('selector-city-search').value = city;
        document.getElementById('selector-city-list').classList.add('hidden');
        loadDistrictsForSelector(city);
    }
    
    async function loadDistrictsForSelector(city) {
        const container = document.getElementById('selector-districts-list');
        container.innerHTML = '<p class="text-gray-400 text-sm col-span-2 text-center py-4">Yükleniyor...</p>';
        
        const districts = await fetchDistricts(city);
        
        if (districts.length === 0) {
            container.innerHTML = '<p class="text-gray-400 text-sm col-span-2 text-center py-4">Bu ilde ilçe bulunamadı</p>';
            return;
        }
        
        container.innerHTML = districts.map(d => {
            const isSelected = selectorSelectedDistricts.some(s => s.id === d.id);
            return `
                <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-gray-50 ${isSelected ? 'bg-green-50 border-green-300' : ''}" 
                       data-district-id="${d.id}" data-district-name="${d.name}">
                    <input type="checkbox" ${isSelected ? 'checked' : ''} 
                           onchange="toggleDistrictSelection(${d.id}, '${d.name.replace(/'/g, "\\'")}', this.checked)"
                           class="rounded border-gray-300 text-black focus:ring-black">
                    <span class="text-sm">${d.name}</span>
                </label>
            `;
        }).join('');
    }
    
    function filterDistrictList(search) {
        const container = document.getElementById('selector-districts-list');
        const items = container.querySelectorAll('label');
        const searchLower = search.toLowerCase();
        
        items.forEach(item => {
            const name = item.dataset.districtName?.toLowerCase() || '';
            item.style.display = name.includes(searchLower) ? '' : 'none';
        });
    }
    
    function toggleDistrictSelection(id, name, isChecked) {
        if (isChecked) {
            if (!selectorSelectedDistricts.some(d => d.id === id)) {
                selectorSelectedDistricts.push({ id, name });
            }
        } else {
            selectorSelectedDistricts = selectorSelectedDistricts.filter(d => d.id !== id);
        }
        
        updateSelectorSelectedUI();
        
        // Checkbox'ın parent label'ını güncelle
        const label = document.querySelector(`label[data-district-id="${id}"]`);
        if (label) {
            label.classList.toggle('bg-green-50', isChecked);
            label.classList.toggle('border-green-300', isChecked);
        }
    }
    
    function removeSelectedDistrict(id) {
        selectorSelectedDistricts = selectorSelectedDistricts.filter(d => d.id !== id);
        updateSelectorSelectedUI();
        
        // Checkbox'ı da güncelle
        const label = document.querySelector(`label[data-district-id="${id}"]`);
        if (label) {
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
            label.classList.remove('bg-green-50', 'border-green-300');
        }
    }
    
    function clearAllSelectedDistricts() {
        selectorSelectedDistricts = [];
        updateSelectorSelectedUI();
        
        // Tüm checkbox'ları temizle
        document.querySelectorAll('#selector-districts-list input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
            cb.closest('label').classList.remove('bg-green-50', 'border-green-300');
        });
    }
    
    function updateSelectorSelectedUI() {
        const container = document.getElementById('selector-selected-container');
        const tagsContainer = document.getElementById('selector-selected-tags');
        const countEl = document.getElementById('selector-selected-count');
        
        countEl.textContent = selectorSelectedDistricts.length;
        
        if (selectorSelectedDistricts.length === 0) {
            container.classList.add('hidden');
            return;
        }
        
        container.classList.remove('hidden');
        tagsContainer.innerHTML = selectorSelectedDistricts.map(d => `
            <span class="selected-tag">
                ${d.name}
                <button type="button" onclick="removeSelectedDistrict(${d.id})">&times;</button>
            </span>
        `).join('');
    }
    
    function applyDistrictSelection() {
        if (selectorSelectedDistricts.length === 0) {
            showToast('En az bir bölge seçmelisiniz', 'error');
            return;
        }
        
        const target = currentSelectorTarget;
        const ids = selectorSelectedDistricts.map(d => d.id);
        const names = selectorSelectedDistricts.map(d => d.name);
        
        // Hidden input'u güncelle
        document.getElementById(`${target}-district-ids`).value = JSON.stringify(ids);
        
        // Label'ı güncelle
        const label = document.getElementById(`${target}-districts-label`);
        if (names.length <= 2) {
            label.textContent = names.join(', ');
        } else {
            label.textContent = `${names.slice(0, 2).join(', ')} +${names.length - 2} bölge`;
        }
        label.classList.remove('text-gray-500');
        label.classList.add('text-black');
        
        // Tag'leri göster
        const tagsContainer = document.getElementById(`${target}-districts-tags`);
        tagsContainer.innerHTML = selectorSelectedDistricts.map(d => `
            <span class="selected-tag">
                ${d.name}
                <button type="button" onclick="removeDistrictFromForm('${target}', ${d.id})">&times;</button>
            </span>
        `).join('');
        
        closeDistrictSelector();
    }
    
    function removeDistrictFromForm(target, id) {
        // Hidden input'tan kaldır
        const input = document.getElementById(`${target}-district-ids`);
        let ids = [];
        try { ids = JSON.parse(input.value) || []; } catch(e) {}
        ids = ids.filter(i => i !== id);
        input.value = JSON.stringify(ids);
        
        // Tag'i kaldır
        const tagsContainer = document.getElementById(`${target}-districts-tags`);
        const tags = tagsContainer.querySelectorAll('.selected-tag');
        tags.forEach(tag => {
            if (tag.querySelector(`button[onclick*="${id}"]`)) {
                tag.remove();
            }
        });
        
        // Label'ı güncelle
        if (ids.length === 0) {
            document.getElementById(`${target}-districts-label`).textContent = 'Bölge seçmek için tıklayın';
            document.getElementById(`${target}-districts-label`).classList.add('text-gray-500');
            document.getElementById(`${target}-districts-label`).classList.remove('text-black');
        }
        
        // selectorSelectedDistricts'i de güncelle
        selectorSelectedDistricts = selectorSelectedDistricts.filter(d => d.id !== id);
    }
    
    // ==================== YARDIMCI FONKSİYONLAR ====================
    
    // İlçeleri API'den çek (cache'li)
    async function fetchDistricts(city) {
        if (districtsCache[city]) {
            return districtsCache[city];
        }
        
        try {
            const response = await fetch(`{{ route('panel.schedule.districts') }}?city=${encodeURIComponent(city)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const districts = await response.json();
            districtsCache[city] = districts;
            return districts;
        } catch (error) {
            console.error('Districts fetch error:', error);
            return [];
        }
    }
    
    // Seçili ilçe ID'lerini al (form submit için)
    function getSelectedDistrictIds(target) {
        const input = document.getElementById(`${target}-district-ids`);
        try {
            return JSON.parse(input.value) || [];
        } catch(e) {
            return [];
        }
    }
    
    // Vardiya seç (takvimden tıklama)
    function selectShift(event) {
        const props = event.extendedProps;
        
        // currentShift'i güncelle
        currentShift = {
            id: event.id,
            title: event.title,
            region_id: props.region_id,
            region_name: props.region_name || '',
            date_raw: event.startStr.split('T')[0],
            start_time: event.startStr.split('T')[1]?.substring(0, 5) || '',
            end_time: event.endStr.split('T')[1]?.substring(0, 5) || '',
            required_couriers: props.required_couriers,
            assigned_count: props.assigned_count,
            remaining_capacity: props.remaining_capacity,
            status: props.status
        };
        
        // Seçili vardiya bilgisini göster
        const infoPanel = document.getElementById('selected-shift-info');
        infoPanel.classList.remove('hidden');
        
        document.getElementById('selected-shift-title').textContent = event.title;
        document.getElementById('selected-shift-district').textContent = props.region_name || 'Bölge Yok';
        document.getElementById('selected-shift-time').textContent = 
            `${currentShift.start_time} - ${currentShift.end_time}`;
        
        const capacityEl = document.getElementById('selected-shift-capacity');
        const isFull = props.assigned_count >= props.required_couriers;
        capacityEl.innerHTML = `Kapasite: <span class="${isFull ? 'text-green-600' : 'text-yellow-600'} font-medium">${props.assigned_count}/${props.required_couriers}</span>`;
        
        // Kuryeleri yükle (bölge için)
        loadCouriersForShift(props.region_id, event.id, currentShift.date_raw);
    }
    
    // Eventleri çek
    function fetchEvents(start, end, callback) {
        let url = `{{ route('panel.schedule.events') }}?start=${start}&end=${end}`;
        if (selectedRegionId) {
            url += `&region_id=${selectedRegionId}`;
        }
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => {
            console.error('Events fetch error:', error);
            callback([]);
        });
    }
    
    // Vardiya modal aç
    function openShiftModal(shiftId = null, startStr = null, endStr = null) {
        const modal = document.getElementById('shift-modal');
        const form = document.getElementById('shift-form');
        const title = document.getElementById('shift-modal-title');
        
        form.reset();
        document.getElementById('shift-id').value = '';
        
        if (shiftId != null && shiftId !== '' && String(shiftId) !== 'undefined') {
            // Düzenleme modu
            title.textContent = 'Vardiya Düzenle';
            // Mevcut verilerle doldur
            fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('shift-id').value = data.id;
                document.getElementById('shift-region').value = data.region_id || '';
                document.getElementById('shift-date').value = data.date_raw;
                document.getElementById('shift-start-time').value = data.start_time;
                document.getElementById('shift-end-time').value = data.end_time;
                document.getElementById('shift-couriers').value = data.required_couriers;
                document.getElementById('shift-title').value = data.title || '';
                document.getElementById('shift-notes').value = data.notes || '';
                selectColor('color-picker', 'shift-color', data.color);
            });
        } else {
            // Yeni oluşturma modu
            title.textContent = 'Yeni Vardiya';
            
            if (startStr) {
                const start = new Date(startStr);
                const end = endStr ? new Date(endStr) : new Date(start.getTime() + 3600000);
                
                document.getElementById('shift-date').value = start.toISOString().split('T')[0];
                document.getElementById('shift-start-time').value = start.toTimeString().slice(0, 5);
                document.getElementById('shift-end-time').value = end.toTimeString().slice(0, 5);
            } else {
                document.getElementById('shift-date').value = new Date().toISOString().split('T')[0];
                document.getElementById('shift-start-time').value = '09:00';
                document.getElementById('shift-end-time').value = '18:00';
            }
            
            // Eğer filtre seçili ise onu varsayılan yap
            if (selectedRegionId) {
                document.getElementById('shift-region').value = selectedRegionId;
            }
        }
        
        modal.classList.remove('hidden');
    }
    
    function closeShiftModal() {
        document.getElementById('shift-modal').classList.add('hidden');
    }
    
    // Vardiya form submit
    function handleShiftSubmit(e) {
        e.preventDefault();
        
        const shiftId = document.getElementById('shift-id').value;
        const regionId = document.getElementById('shift-region').value;
        
        if (!regionId) {
            showToast('Bölge seçimi zorunludur', 'error');
            return;
        }
        
        const data = {
            region_id: parseInt(regionId),
            shift_date: document.getElementById('shift-date').value,
            start_time: document.getElementById('shift-start-time').value,
            end_time: document.getElementById('shift-end-time').value,
            required_couriers: parseInt(document.getElementById('shift-couriers').value) || 1,
            title: document.getElementById('shift-title').value || null,
            notes: document.getElementById('shift-notes').value || null,
            color: document.getElementById('shift-color').value
        };
        
        const url = shiftId 
            ? `{{ url('panel/schedule/shifts') }}/${shiftId}`
            : '{{ route('panel.schedule.shifts.store') }}';
        
        fetch(url, {
            method: shiftId ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(r => {
            return r.json().then(body => ({ ok: r.ok, status: r.status, body }));
        })
        .then(({ ok, status, body: response }) => {
            if (ok && response.success) {
                closeShiftModal();
                selectedRegionId = '';
                document.getElementById('region-filter').value = '';
                calendar.refetchEvents();
                showToast(response.message || 'Vardiya eklendi.', 'success');
            } else {
                let msg = response.message || 'Bir hata oluştu';
                if (status === 422 && response.errors) {
                    const first = Object.values(response.errors).flat()[0];
                    if (first) msg = first;
                }
                showToast(msg, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('İstek gönderilemedi. Ağ veya sunucu hatası.', 'error');
        });
    }
    
    // Detay modal
    function openDetailModal(shiftId) {
        if (shiftId == null || shiftId === '' || String(shiftId) === 'undefined') {
            showToast('Vardiya bilgisi yüklenemedi.', 'error');
            return;
        }
        const modal = document.getElementById('detail-modal');
        const content = document.getElementById('detail-content');
        
        content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-black mx-auto"></div></div>';
        modal.classList.remove('hidden');
        currentShift = { id: shiftId };
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json().then(body => ({ ok: r.ok, status: r.status, body })))
        .then(({ ok, body: data }) => {
            if (!ok || data.id == null) {
                closeDetailModal();
                showToast(data?.message || 'Vardiya yüklenemedi.', 'error');
                return;
            }
            currentShift = data;
            renderDetailContent(data);
            loadCouriersForShift(data.region_id, shiftId, data.date_raw);
        })
        .catch(() => {
            closeDetailModal();
            showToast('Vardiya yüklenemedi.', 'error');
        });
    }
    
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
        currentShift = null;
    }
    
    function renderDetailContent(data) {
        if (data.id == null) return;
        const statusColors = {
            draft: 'yellow',
            published: 'blue',
            completed: 'green',
            cancelled: 'red'
        };
        const statusLabels = {
            draft: 'Taslak',
            published: 'Yayında',
            completed: 'Tamamlandı',
            cancelled: 'İptal'
        };
        
        document.getElementById('detail-title').textContent = data.title;
        
        let assignmentsHtml = '';
        if (data.assignments && data.assignments.length > 0) {
            assignmentsHtml = data.assignments.map(a => `
                <div class="assignment-item flex items-center justify-between py-2 px-3 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">
                            ${a.courier_name.charAt(0)}
                        </div>
                        <div>
                            <p class="font-medium text-sm">${a.courier_name}</p>
                            <p class="text-xs text-gray-500">${a.courier_phone || ''}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-${a.status_color}-100 text-${a.status_color}-800">
                            ${a.status_label}
                        </span>
                        <button onclick="unassignCourier(${data.id}, ${a.id})" 
                                class="text-red-500 hover:text-red-700 p-1" title="Kaldır">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            assignmentsHtml = '<p class="text-gray-500 text-sm text-center py-4">Henüz kurye atanmamış</p>';
        }
        
        document.getElementById('detail-content').innerHTML = `
            <div class="grid grid-cols-2 gap-6">
                <!-- Sol: Vardiya Bilgileri -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 text-sm rounded-full bg-${statusColors[data.status]}-100 text-${statusColors[data.status]}-800">
                            ${statusLabels[data.status]}
                        </span>
                        <span class="w-4 h-4 rounded" style="background-color: ${data.color}"></span>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Bölge</p>
                        <p class="font-medium">${data.region_name || '-'}</p>
                        ${data.districts ? `<p class="text-xs text-gray-500 mt-1">İlçeler: ${data.districts}</p>` : ''}
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Tarih ve Saat</p>
                        <p class="font-medium">${data.date}</p>
                        <p class="text-sm">${data.start_time} - ${data.end_time} (${data.duration})</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Kurye Kapasitesi</p>
                        <p class="font-medium">
                            <span class="${data.assigned_count >= data.required_couriers ? 'text-green-600' : 'text-yellow-600'}">
                                ${data.assigned_count}
                            </span> / ${data.required_couriers}
                        </p>
                    </div>
                    
                    ${data.notes ? `
                    <div>
                        <p class="text-sm text-gray-500">Notlar</p>
                        <p class="text-sm">${data.notes}</p>
                    </div>
                    ` : ''}
                    
                    <div class="flex gap-2 pt-4">
                        <button onclick="openShiftModal(${data.id})" 
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Düzenle
                        </button>
                        <button onclick="duplicateShift(${data.id})" 
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Kopyala
                        </button>
                        <button onclick="deleteShift(${data.id})" 
                                class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                            Sil
                        </button>
                    </div>
                </div>
                
                <!-- Sağ: Atanmış Kuryeler -->
                <div>
                    <h3 class="font-semibold mb-3">Atanmış Kuryeler</h3>
                    <div class="space-y-2 drop-zone" id="assignments-drop-zone">
                        ${assignmentsHtml}
                    </div>
                    
                    ${data.remaining_capacity > 0 ? `
                    <p class="text-sm text-gray-500 mt-3">
                        Sağdaki listeden kurye sürükleyip bırakabilirsiniz.
                    </p>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Drop zone setup
        setupDropZone();
    }
    
    // Kuryeler için drop zone
    function setupDropZone() {
        const dropZone = document.getElementById('assignments-drop-zone');
        if (!dropZone) return;
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const courierId = e.dataTransfer.getData('text/plain');
            if (currentShift && courierId) {
                assignCourier(currentShift.id, courierId);
            }
        });
    }
    
    // Kuryeler listesini yükle
    function loadCouriersForShift(regionId, shiftId, date) {
        const url = `{{ route('panel.schedule.couriers') }}?region_id=${regionId || ''}&shift_id=${shiftId}&date=${date}`;
        
        // Yükleniyor göster
        document.getElementById('courier-list').innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-black mx-auto"></div></div>';
        
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const couriers = Array.isArray(data.couriers) ? data.couriers : [];
            const countEl = document.getElementById('courier-count');
            const assignedCount = couriers.filter(c => c.is_assigned).length;
            countEl.textContent = `${assignedCount} atanmış / ${couriers.length} toplam`;
            renderCourierList(couriers);
        })
        .catch(err => {
            console.error('Kurye listesi yüklenemedi:', err);
            document.getElementById('courier-list').innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Kuryeler yüklenirken hata oluştu.</p>';
            document.getElementById('courier-count').textContent = '';
        });
    }
    
    function renderCourierList(couriers) {
        const container = document.getElementById('courier-list');
        
        if (!currentShift) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Kurye atamak için bir vardiya seçin</p>';
            return;
        }
        
        if (couriers.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Bu bölgede kurye bulunamadı</p>';
            return;
        }
        
        container.innerHTML = couriers.map(c => `
            <div class="courier-card p-3 bg-gray-50 rounded-lg ${c.is_assigned ? 'assigned' : ''} ${c.is_busy ? 'busy' : ''}"
                 draggable="${!c.is_assigned && !c.is_busy}"
                 data-courier-id="${c.id}"
                 data-courier-name="${c.name}"
                 data-assignment-id="${c.assignment_id || ''}">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium flex-shrink-0">
                        ${c.name.charAt(0)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">${c.name}</p>
                        <p class="text-xs text-gray-500">${c.phone || ''}</p>
                    </div>
                    <div class="flex-shrink-0">
                        ${c.is_assigned ? `
                            <button onclick="removeCourierFromList(${currentShift.id}, ${c.assignment_id})" 
                                    class="px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition-colors">
                                Çıkar
                            </button>
                        ` : c.is_busy ? `
                            <span class="px-2 py-1 text-xs text-yellow-600 bg-yellow-50 rounded">Meşgul</span>
                        ` : `
                            <button onclick="addCourierFromList(${c.id})" 
                                    class="px-2 py-1 text-xs font-medium text-green-600 bg-green-50 rounded hover:bg-green-100 transition-colors">
                                Ekle
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `).join('');
        
        // Drag event'leri ekle (sadece atanmamış ve meşgul olmayan kartlar için)
        container.querySelectorAll('.courier-card[draggable="true"]').forEach(card => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', card.dataset.courierId);
                card.classList.add('dragging');
            });
            
            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
            });
        });
    }
    
    // Listeden kurye ekle
    function addCourierFromList(courierId) {
        if (!currentShift) {
            showToast('Önce bir vardiya seçin', 'error');
            return;
        }
        assignCourier(currentShift.id, courierId);
    }
    
    // Listeden kurye çıkar
    function removeCourierFromList(shiftId, assignmentId) {
        if (!confirm('Bu kuryeyi vardiyadan çıkarmak istediğinize emin misiniz?')) return;
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}/assign/${assignmentId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message, 'success');
                // Kurye listesini yenile
                if (currentShift) {
                    loadCouriersForShift(currentShift.region_id, currentShift.id, currentShift.date_raw);
                    // Kapasite bilgisini güncelle
                    updateCapacityDisplay(response.shift?.extendedProps);
                }
                calendar.refetchEvents();
            } else {
                showToast(response.message, 'error');
            }
        });
    }
    
    function filterCouriers(search) {
        const cards = document.querySelectorAll('.courier-card');
        const searchLower = search.toLowerCase();
        
        cards.forEach(card => {
            const name = card.dataset.courierName.toLowerCase();
            card.style.display = name.includes(searchLower) ? '' : 'none';
        });
    }
    
    // Kurye ata
    function assignCourier(shiftId, courierId) {
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ courier_id: courierId })
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message, 'success');
                // Kurye listesini yenile
                if (currentShift) {
                    loadCouriersForShift(currentShift.region_id, currentShift.id, currentShift.date_raw);
                    // Kapasite bilgisini güncelle
                    updateCapacityDisplay(response.shift?.extendedProps);
                }
                calendar.refetchEvents();
            } else {
                showToast(response.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Bir hata oluştu', 'error');
        });
    }
    
    // Kapasite gösterimini güncelle
    function updateCapacityDisplay(props) {
        if (!props) return;
        
        const capacityEl = document.getElementById('selected-shift-capacity');
        if (capacityEl) {
            const isFull = props.assigned_count >= props.required_couriers;
            capacityEl.innerHTML = `Kapasite: <span class="${isFull ? 'text-green-600' : 'text-yellow-600'} font-medium">${props.assigned_count}/${props.required_couriers}</span>`;
        }
        
        // currentShift'i de güncelle
        if (currentShift) {
            currentShift.assigned_count = props.assigned_count;
            currentShift.remaining_capacity = props.remaining_capacity;
        }
    }
    
    // Kurye atamasını kaldır
    function unassignCourier(shiftId, assignmentId) {
        if (!confirm('Bu kurye atamasını kaldırmak istediğinize emin misiniz?')) return;
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}/assign/${assignmentId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message, 'success');
                openDetailModal(shiftId);
                calendar.refetchEvents();
            } else {
                showToast(response.message, 'error');
            }
        });
    }
    
    // Vardiya taşı
    function moveShift(shiftId, startStr, endStr) {
        const start = new Date(startStr);
        const end = new Date(endStr);
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}/move`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                shift_date: start.toISOString().split('T')[0],
                start_time: start.toTimeString().slice(0, 5),
                end_time: end.toTimeString().slice(0, 5)
            })
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast('Vardiya taşındı', 'success');
            } else {
                showToast(response.message, 'error');
                calendar.refetchEvents();
            }
        });
    }
    
    // Vardiya kopyala
    function duplicateShift(shiftId) {
        const newDate = prompt('Kopyalanacak tarih (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
        if (!newDate) return;
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ shift_date: newDate })
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message, 'success');
                closeDetailModal();
                calendar.refetchEvents();
            } else {
                showToast(response.message, 'error');
            }
        });
    }
    
    // Vardiya sil
    function deleteShift(shiftId) {
        if (shiftId == null || shiftId === '' || String(shiftId) === 'undefined') {
            showToast('Vardiya seçilmedi.', 'error');
            return;
        }
        if (!confirm('Bu vardiyayı silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz ve vardiyadaki tüm kurye atamaları da silinecektir.')) return;
        
        fetch(`{{ url('panel/schedule/shifts') }}/${shiftId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message || 'Vardiya başarıyla silindi', 'success');
                closeDetailModal();
                calendar.refetchEvents();
                // Sağ paneli de sıfırla
                document.getElementById('selected-shift-info').classList.add('hidden');
                document.getElementById('courier-list').innerHTML = '<p class="text-gray-500 text-sm text-center py-4">Takvimden bir vardiya seçin</p>';
                currentShift = null;
            } else {
                showToast(response.message || 'Bir hata oluştu', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showToast('Bir hata oluştu', 'error');
        });
    }
    
    // Toplu oluşturma modal
    function openBulkModal() {
        document.getElementById('bulk-modal').classList.remove('hidden');
        document.getElementById('bulk-form').reset();
        
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('bulk-start-date').value = today;
        
        if (selectedDistrictId) {
            document.getElementById('bulk-district').value = selectedDistrictId;
        }
    }
    
    function closeBulkModal() {
        document.getElementById('bulk-modal').classList.add('hidden');
    }

    function closeExcelUploadModal() {
        document.getElementById('excel-upload-modal').classList.add('hidden');
    }
    
    function handleBulkSubmit(e) {
        e.preventDefault();
        
        const regionId = document.getElementById('bulk-region').value;
        
        if (!regionId) {
            showToast('Bölge seçimi zorunludur', 'error');
            return;
        }
        
        const formData = new FormData(e.target);
        const data = {
            region_id: parseInt(regionId),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            start_time: formData.get('start_time'),
            end_time: formData.get('end_time'),
            required_couriers: parseInt(formData.get('required_couriers')),
            color: formData.get('color')
        };
        
        fetch('{{ route('panel.schedule.bulk-create') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(response => {
            if (response.success) {
                showToast(response.message, 'success');
                closeBulkModal();
                calendar.refetchEvents();
            } else {
                showToast(response.message || 'Bir hata oluştu', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Bir hata oluştu', 'error');
        });
    }
    
    // Renk seçici
    function setupColorPicker(pickerId, inputId) {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        
        picker.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', () => {
                picker.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                input.value = option.dataset.color;
            });
        });
    }
    
    function selectColor(pickerId, inputId, color) {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        
        picker.querySelectorAll('.color-option').forEach(o => {
            o.classList.toggle('selected', o.dataset.color === color);
        });
        input.value = color;
    }
    
    // Toast mesajları
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush
