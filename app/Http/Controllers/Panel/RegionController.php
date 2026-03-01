<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\District;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Türkiye illeri listesi
     */
    private function getCities(): array
    {
        return [
            'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Aksaray', 'Amasya', 'Ankara', 'Antalya', 'Ardahan', 'Artvin',
            'Aydın', 'Balıkesir', 'Bartın', 'Batman', 'Bayburt', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur',
            'Bursa', 'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Düzce', 'Edirne', 'Elazığ', 'Erzincan',
            'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkari', 'Hatay', 'Iğdır', 'Isparta', 'İstanbul',
            'İzmir', 'Kahramanmaraş', 'Karabük', 'Karaman', 'Kars', 'Kastamonu', 'Kayseri', 'Kırıkkale', 'Kırklareli', 'Kırşehir',
            'Kilis', 'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Mardin', 'Mersin', 'Muğla', 'Muş',
            'Nevşehir', 'Niğde', 'Ordu', 'Osmaniye', 'Rize', 'Sakarya', 'Samsun', 'Siirt', 'Sinop', 'Sivas',
            'Şanlıurfa', 'Şırnak', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Uşak', 'Van', 'Yalova', 'Yozgat', 'Zonguldak'
        ];
    }

    /**
     * Bölge listesi
     */
    public function index(Request $request)
    {
        $query = Region::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        $regions = $query->orderBy('city')->orderBy('name')->paginate(20);

        return view('panel.regions.index', compact('regions'));
    }

    /**
     * Bölge oluşturma formu
     */
    public function create()
    {
        $cities = $this->getCities();
        return view('panel.regions.create', compact('cities'));
    }

    /**
     * Bölge adında Türkçe karakter var mı kontrol et
     */
    private function nameHasTurkishChars(string $value): bool
    {
        return (bool) preg_match('/[çğıöşüÇĞİÖŞÜâîûÂÎÛ]/u', $value);
    }

    /**
     * Yeni bölge kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->nameHasTurkishChars($value)) {
                        $fail('Bölge adında Türkçe karakter (ç, ğ, ı, ö, ş, ü, â, î, û vb.) kullanılamaz. Sadece İngilizce harfler ve rakam kullanın.');
                    }
                },
            ],
            'city' => 'required|string|max:100',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Bölge adı zorunludur.',
            'city.required' => 'İl seçimi zorunludur.',
        ]);

        $region = Region::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bölge başarıyla oluşturuldu.',
                'region' => $region,
            ]);
        }

        return redirect()->route('panel.regions.index')
            ->with('success', 'Bölge başarıyla oluşturuldu.');
    }

    /**
     * Bölge detayı
     */
    public function show(Region $region)
    {
        return view('panel.regions.show', compact('region'));
    }

    /**
     * Bölge düzenleme formu
     */
    public function edit(Region $region)
    {
        $cities = $this->getCities();
        return view('panel.regions.edit', compact('region', 'cities'));
    }

    /**
     * Bölge güncelle
     */
    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->nameHasTurkishChars($value)) {
                        $fail('Bölge adında Türkçe karakter (ç, ğ, ı, ö, ş, ü, â, î, û vb.) kullanılamaz. Sadece İngilizce harfler ve rakam kullanın.');
                    }
                },
            ],
            'city' => 'required|string|max:100',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'city.required' => 'İl seçimi zorunludur.',
        ]);

        $region->update([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bölge başarıyla güncellendi.',
                'region' => $region,
            ]);
        }

        return redirect()->route('panel.regions.index')
            ->with('success', 'Bölge başarıyla güncellendi.');
    }

    /**
     * Bölge sil
     */
    public function destroy(Region $region)
    {
        $region->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bölge başarıyla silindi.',
            ]);
        }

        return redirect()->route('panel.regions.index')
            ->with('success', 'Bölge başarıyla silindi.');
    }

    /**
     * Tüm bölgeleri JSON olarak döndür (AJAX)
     */
    public function list(Request $request)
    {
        $regions = Region::active()
            ->orderBy('name')
            ->get()
            ->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->name,
                    'city' => $region->city,
                    'color' => $region->color,
                ];
            });

        return response()->json($regions);
    }
}
