<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartShiftRequest;
use App\Http\Requests\EndShiftRequest;
use App\Models\Shift;
use App\Models\ShiftLog;
use App\Models\ShiftPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Vardiya API Controller
 * 
 * Kurye mobil uygulaması için vardiya işlemleri.
 */
class ShiftController extends Controller
{
    /**
     * Kurye kendi vardiyalarını listele
     * 
     * @queryParam date string Tarih filtresi (Y-m-d formatında)
     * @queryParam status string Durum filtresi (active, completed, cancelled)
     * @queryParam per_page int Sayfa başına kayıt (default: 15)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Kurye değilse hata döndür
        if (!$user->isCourier()) {
            return $this->unauthorized('Bu endpoint sadece kuryeler için geçerlidir');
        }

        $query = $user->shifts()->with(['district', 'photos']);

        // Tarih filtresi
        if ($request->has('date')) {
            $query->whereDate('started_at', $request->date);
        }

        // Durum filtresi
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sıralama
        $query->orderBy('started_at', 'desc');

        // Sayfalama
        $shifts = $query->paginate($request->get('per_page', 15));

        return $this->success([
            'shifts' => $shifts->items(),
            'pagination' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    /**
     * Aktif vardiyayı getir
     */
    public function active(Request $request)
    {
        $user = $request->user();

        if (!$user->isCourier()) {
            return $this->unauthorized('Bu endpoint sadece kuryeler için geçerlidir');
        }

        $shift = $user->activeShift();

        if (!$shift) {
            return $this->success(['shift' => null], 'Aktif vardiya bulunmuyor');
        }

        $shift->load(['district', 'photos', 'logs']);

        return $this->success([
            'shift' => [
                'id' => $shift->id,
                'status' => $shift->status,
                'started_at' => $shift->started_at->toIso8601String(),
                'duration_minutes' => $shift->getDurationInMinutes(),
                'formatted_duration' => $shift->formatted_duration,
                'start_location' => [
                    'latitude' => $shift->start_latitude,
                    'longitude' => $shift->start_longitude,
                    'address' => $shift->start_address,
                    'map_url' => $shift->start_location_url,
                ],
                'district' => $shift->district ? [
                    'id' => $shift->district->id,
                    'name' => $shift->district->name,
                ] : null,
                'start_photos' => $shift->startPhotos->map(fn($p) => [
                    'id' => $p->id,
                    'url' => $p->url,
                ]),
            ],
        ]);
    }

    /**
     * Vardiya başlat
     * 
     * @bodyParam latitude float required Enlem
     * @bodyParam longitude float required Boylam
     * @bodyParam photo file Başlangıç fotoğrafı
     * @bodyParam device_id string Cihaz kimliği
     * @bodyParam device_model string Cihaz modeli
     */
    public function start(StartShiftRequest $request)
    {
        $user = $request->user();

        // Yetki kontrolü (Policy)
        $this->authorize('start', Shift::class);

        // Zaten aktif vardiya var mı kontrol et
        if ($user->hasActiveShift()) {
            return $this->error('Zaten aktif bir vardiyanız bulunuyor. Önce mevcut vardiyayı bitirmelisiniz.', 422);
        }

        // Kuryenin ana bölgesini otomatik olarak al
        $primaryDistrict = $user->courierDistricts()->wherePivot('is_primary', true)->first();

        DB::beginTransaction();

        try {
            // Vardiyayı oluştur (bölge otomatik atanır)
            $shift = Shift::startNew($user, [
                'district_id' => $primaryDistrict?->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]);

            // Başlangıç logunu oluştur
            ShiftLog::createFromRequest($shift, ShiftLog::TYPE_START, [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'accuracy' => $request->accuracy,
                'device_id' => $request->device_id,
                'device_model' => $request->device_model,
                'os_version' => $request->os_version,
                'app_version' => $request->app_version,
            ], $request);

            // Fotoğraf yükleme
            if ($request->hasFile('photo')) {
                ShiftPhoto::createFromUpload($shift, ShiftPhoto::TYPE_START, $request->file('photo'));
            }

            DB::commit();

            // Yanıt için vardiyayı yükle
            $shift->load(['district', 'startPhotos']);

            return $this->success([
                'shift' => [
                    'id' => $shift->id,
                    'status' => $shift->status,
                    'started_at' => $shift->started_at->toIso8601String(),
                    'start_location' => [
                        'latitude' => $shift->start_latitude,
                        'longitude' => $shift->start_longitude,
                        'address' => $shift->start_address,
                    ],
                    'district' => $shift->district ? [
                        'id' => $shift->district->id,
                        'name' => $shift->district->name,
                    ] : null,
                ],
            ], 'Vardiya başarıyla başlatıldı', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Vardiya başlatılırken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Vardiya bitir
     * 
     * @bodyParam latitude float required Enlem
     * @bodyParam longitude float required Boylam
     * @bodyParam package_count int required Atılan paket sayısı
     * @bodyParam photo file Bitiş fotoğrafı
     * @bodyParam notes string Kurye notları
     */
    public function end(EndShiftRequest $request, Shift $shift)
    {
        $user = $request->user();

        // Yetki kontrolü (Policy)
        $this->authorize('end', $shift);

        // Durum kontrolü
        if (!$shift->isActive()) {
            return $this->error('Bu vardiya zaten tamamlanmış veya iptal edilmiş.', 422);
        }

        DB::beginTransaction();

        try {
            // Vardiyayı tamamla
            $shift->complete([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'package_count' => $request->package_count,
                'notes' => $request->notes,
            ]);

            // Bitiş logunu oluştur
            ShiftLog::createFromRequest($shift, ShiftLog::TYPE_END, [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'accuracy' => $request->accuracy,
                'device_id' => $request->device_id,
                'device_model' => $request->device_model,
                'os_version' => $request->os_version,
                'app_version' => $request->app_version,
            ], $request);

            // Fotoğraf yükleme
            if ($request->hasFile('photo')) {
                ShiftPhoto::createFromUpload($shift, ShiftPhoto::TYPE_END, $request->file('photo'));
            }

            DB::commit();

            // Yanıt için vardiyayı yükle
            $shift->refresh();
            $shift->load(['district', 'photos']);

            return $this->success([
                'shift' => [
                    'id' => $shift->id,
                    'status' => $shift->status,
                    'started_at' => $shift->started_at->toIso8601String(),
                    'ended_at' => $shift->ended_at->toIso8601String(),
                    'total_minutes' => $shift->total_minutes,
                    'formatted_duration' => $shift->formatted_duration,
                    'package_count' => $shift->package_count,
                    'start_location' => [
                        'latitude' => $shift->start_latitude,
                        'longitude' => $shift->start_longitude,
                        'address' => $shift->start_address,
                        'map_url' => $shift->start_location_url,
                    ],
                    'end_location' => [
                        'latitude' => $shift->end_latitude,
                        'longitude' => $shift->end_longitude,
                        'address' => $shift->end_address,
                        'map_url' => $shift->end_location_url,
                    ],
                ],
            ], 'Vardiya başarıyla tamamlandı');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Vardiya tamamlanırken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Vardiya detayını görüntüle
     */
    public function show(Request $request, Shift $shift)
    {
        // Yetki kontrolü (Policy)
        $this->authorize('view', $shift);

        $shift->load(['district', 'photos', 'logs']);

        return $this->success([
            'shift' => [
                'id' => $shift->id,
                'status' => $shift->status,
                'started_at' => $shift->started_at->toIso8601String(),
                'ended_at' => $shift->ended_at?->toIso8601String(),
                'total_minutes' => $shift->total_minutes,
                'formatted_duration' => $shift->formatted_duration,
                'package_count' => $shift->package_count,
                'notes' => $shift->notes,
                'start_location' => [
                    'latitude' => $shift->start_latitude,
                    'longitude' => $shift->start_longitude,
                    'address' => $shift->start_address,
                    'map_url' => $shift->start_location_url,
                ],
                'end_location' => $shift->ended_at ? [
                    'latitude' => $shift->end_latitude,
                    'longitude' => $shift->end_longitude,
                    'address' => $shift->end_address,
                    'map_url' => $shift->end_location_url,
                ] : null,
                'district' => $shift->district ? [
                    'id' => $shift->district->id,
                    'name' => $shift->district->name,
                    'city' => $shift->district->city,
                ] : null,
                'photos' => [
                    'start' => $shift->startPhotos->map(fn($p) => [
                        'id' => $p->id,
                        'url' => $p->url,
                        'taken_at' => $p->exif_taken_at?->toIso8601String(),
                    ]),
                    'end' => $shift->endPhotos->map(fn($p) => [
                        'id' => $p->id,
                        'url' => $p->url,
                        'taken_at' => $p->exif_taken_at?->toIso8601String(),
                    ]),
                ],
            ],
        ]);
    }

    /**
     * Vardiyaya ek fotoğraf yükle
     * 
     * @bodyParam photo file required Fotoğraf dosyası
     * @bodyParam type string required Fotoğraf tipi (start/end)
     */
    public function uploadPhoto(Request $request, Shift $shift)
    {
        // Yetki kontrolü
        $this->authorize('update', $shift);

        $request->validate([
            'photo' => 'required|image|max:10240', // Max 10MB
            'type' => 'required|in:start,end',
        ]);

        // Bitiş fotoğrafı sadece aktif vardiyaya yüklenebilir
        if ($request->type === 'end' && !$shift->isActive()) {
            return $this->error('Bitiş fotoğrafı sadece aktif vardiyaya yüklenebilir.', 422);
        }

        $photo = ShiftPhoto::createFromUpload($shift, $request->type, $request->file('photo'));

        return $this->success([
            'photo' => [
                'id' => $photo->id,
                'url' => $photo->url,
                'type' => $photo->type,
            ],
        ], 'Fotoğraf başarıyla yüklendi', 201);
    }

    /**
     * Vardiya istatistikleri
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if (!$user->isCourier()) {
            return $this->unauthorized();
        }

        // Bu ayki istatistikler
        $thisMonth = $user->shifts()
            ->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->where('status', 'completed');

        // Bugünkü istatistikler
        $today = $user->shifts()
            ->whereDate('started_at', today())
            ->where('status', 'completed');

        return $this->success([
            'today' => [
                'shift_count' => $today->count(),
                'total_packages' => $today->sum('package_count'),
                'total_minutes' => $today->sum('total_minutes'),
            ],
            'this_month' => [
                'shift_count' => $thisMonth->count(),
                'total_packages' => $thisMonth->sum('package_count'),
                'total_minutes' => $thisMonth->sum('total_minutes'),
            ],
        ]);
    }
}
