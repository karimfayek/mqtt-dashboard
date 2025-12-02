<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reading;

class StatsController extends Controller
{
    public function latest()
    {
        $readings = Reading::orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return response()->json($readings);
    }

    public function toiletsSummary()
    {
        $summary = Reading::selectRaw('toilet_id, sensor_type, COUNT(*) as count')
            ->groupBy('toilet_id', 'sensor_type')
            ->orderBy('toilet_id')
            ->get();

        return response()->json($summary);
    }
    public function usagePerToilet()
    {
        $intervalSeconds = 5; // نفس الـ interval بتاع publisher.js

        // نشتغل بس على occupancy
        $rows = DB::table('readings')
            ->selectRaw("
                toilet_id,
                COUNT(*) FILTER (WHERE (payload->>'occupied')::boolean = true) AS occupied_hits,
                COUNT(*) AS total_hits
            ")
            ->where('sensor_type', 'occupancy')
            ->groupBy('toilet_id')
            ->orderBy('toilet_id')
            ->get();

        $result = $rows->map(function ($row) use ($intervalSeconds) {
            $totalOccupiedSeconds = $row->occupied_hits * $intervalSeconds;
            $visitsApprox = max($row->occupied_hits, 1); // عشان ما نقسمش على صفر
            $avgVisitSeconds = $totalOccupiedSeconds / $visitsApprox;

            return [
                'toilet_id' => $row->toilet_id,
                'occupied_hits' => (int) $row->occupied_hits,
                'total_hits' => (int) $row->total_hits,
                'total_occupied_seconds' => $totalOccupiedSeconds,
                'total_occupied_minutes' => round($totalOccupiedSeconds / 60, 1),
                'avg_visit_seconds_approx' => round($avgVisitSeconds, 1),
                'avg_visit_minutes_approx' => round($avgVisitSeconds / 60, 2),
            ];
        });

        return response()->json($result);
    }

    // ✅ استخدام بالساعة لكل حمام (Heatmap / Line Chart)
    public function hourlyUsage()
    {
        $intervalSeconds = 5;

        $rows = DB::table('readings')
            ->selectRaw("
                toilet_id,
                date_trunc('hour', created_at) as hour,
                COUNT(*) FILTER (WHERE (payload->>'occupied')::boolean = true) AS occupied_hits
            ")
            ->where('sensor_type', 'occupancy')
            ->groupBy('toilet_id', DB::raw("date_trunc('hour', created_at)"))
            ->orderBy('hour')
            ->orderBy('toilet_id')
            ->get();

        $data = $rows->map(function ($row) use ($intervalSeconds) {
            $seconds = $row->occupied_hits * $intervalSeconds;

            return [
                'toilet_id' => $row->toilet_id,
                'hour' => $row->hour,
                'occupied_hits' => (int) $row->occupied_hits,
                'occupied_minutes' => round($seconds / 60, 1),
            ];
        });

        return response()->json($data);
    }
}

