@extends('layouts.panel')

@section('title', 'Masraf Talepleri')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Masraf Talepleri</h1>
        <p class="text-gray-500 mt-1">İnceleme bekleyen kurye masraf talepleri.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.expenses.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
        <a href="{{ route('panel.expenses.history') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            Geçmiş Masraf Talepleri →
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
@endif

@if($requests->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
        İnceleme bekleyen masraf talebi yok.
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tutar</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $req->user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($req->total_amount, 2, ',', '.') }} TL</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $req->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-right">
                                <a href="{{ route('panel.expenses.show', $req) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">İncele</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">{{ $requests->links() }}</div>
    </div>
@endif
@endsection
