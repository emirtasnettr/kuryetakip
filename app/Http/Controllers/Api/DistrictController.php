<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

/**
 * İlçe API Controller
 * 
 * İlçe listesi ve detay endpoint'leri.
 */
class DistrictController extends Controller
{
    /**
     * Aktif ilçeleri listele
     * 
     * @queryParam city string Şehir filtresi
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = District::active()->orderBy('name');

        // Şehir filtresi
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Kurye ise sadece kendi ilçelerini göster
        if ($user->isCourier()) {
            $query->whereIn('id', $user->courierDistricts()->pluck('districts.id'));
        }

        $districts = $query->get();

        return $this->success([
            'districts' => $districts->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'city' => $d->city,
                'code' => $d->code,
                'full_name' => $d->full_name,
            ]),
        ]);
    }

    /**
     * İlçe detayı
     */
    public function show(Request $request, District $district)
    {
        return $this->success([
            'district' => [
                'id' => $district->id,
                'name' => $district->name,
                'city' => $district->city,
                'code' => $district->code,
                'is_active' => $district->is_active,
            ],
        ]);
    }
}
