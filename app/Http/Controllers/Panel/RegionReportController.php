<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\ScheduledShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Bölge Raporu: Planlanan vs gerçekleşen vardiya saatleri.
 */
class RegionReportController extends Controller
{
    /**
     * Bölge raporu sayfası (tarih filtresi ile)
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $rows = $this->buildReportRows($start, $end);

        return view('panel.regions.report', [
            'rows' => $rows,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ]);
    }

    /**
     * Tarih aralığına göre bölge bazlı planlanan / gerçekleşen saatleri hesapla.
     * @return Collection<int, array{region_id: int, region_name: string, city: ?string, planned_minutes: int, actual_minutes: int, diff_minutes: int}>
     */
    public function buildReportRows(Carbon $start, Carbon $end): Collection
    {
        $regions = Region::active()->orderBy('city')->orderBy('name')->get();

        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $plannedByRegion = ScheduledShift::query()
            ->whereBetween('shift_date', [$startStr, $endStr])
            ->whereNotNull('region_id')
            ->get()
            ->groupBy('region_id')
            ->map(function ($shifts) {
                return $shifts->sum(function ($s) {
                    $rawStart = $s->getRawOriginal('start_time');
                    $rawEnd = $s->getRawOriginal('end_time');
                    if ($rawStart === null || $rawEnd === null) {
                        return 0;
                    }
                    $start = Carbon::parse($rawStart);
                    $end = Carbon::parse($rawEnd);
                    if ($end->lt($start)) {
                        $end = $end->copy()->addDay();
                    }
                    return (int) $start->diffInMinutes($end);
                });
            });

        $actualByRegion = Shift::query()
            ->where('status', Shift::STATUS_COMPLETED)
            ->whereNotNull('region_id')
            ->whereDate('started_at', '>=', $startStr)
            ->whereDate('started_at', '<=', $endStr)
            ->selectRaw('region_id, COALESCE(SUM(total_minutes), 0) as total')
            ->groupBy('region_id')
            ->pluck('total', 'region_id');

        return $regions->map(function (Region $region) use ($plannedByRegion, $actualByRegion) {
            $planned = (int) ($plannedByRegion->get($region->id, 0));
            $actual = (int) ($actualByRegion->get($region->id, 0));
            $diff = $planned - $actual;

            return [
                'region_id' => $region->id,
                'region_name' => $region->name,
                'city' => $region->city,
                'planned_minutes' => $planned,
                'actual_minutes' => $actual,
                'diff_minutes' => $diff,
                'planned_formatted' => self::formatMinutes($planned),
                'actual_formatted' => self::formatMinutes($actual),
                'diff_formatted' => self::formatMinutes(abs($diff)),
                'diff_label' => $diff > 0 ? 'Eksik' : ($diff < 0 ? 'Fazla' : 'Tam'),
            ];
        });
    }

    /**
     * Saat formatı (dakika -> X sa Y dk)
     */
    public static function formatMinutes(int $minutes): string
    {
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) {
            return "{$h} sa {$m} dk";
        }
        if ($h > 0) {
            return "{$h} sa";
        }
        return "{$m} dk";
    }
}
