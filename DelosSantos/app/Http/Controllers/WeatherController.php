<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $coordinates = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $key = config('services.openweather.key');
        if (! $key) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 503);
        }

        $lat = round((float) $coordinates['lat'], 4);
        $lon = round((float) $coordinates['lon'], 4);
        $cacheKey = 'puihaha.weather.'.str_replace('.', '_', (string) $lat).'_'.str_replace('.', '_', (string) $lon);

        try {
            $weather = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($key, $lat, $lon): array {
                $response = Http::timeout(8)->get(config('services.openweather.base_url'), [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $key,
                    'units' => 'metric',
                ]);

                if ($response->failed()) {
                    throw new \RuntimeException('Weather provider request failed.');
                }

                $payload = $response->json();

                return [
                    'place' => data_get($payload, 'name', 'Selected location'),
                    'temperature' => round((float) data_get($payload, 'main.temp', 0), 1),
                    'description' => ucfirst((string) data_get($payload, 'weather.0.description', 'Unavailable')),
                    'humidity' => (int) data_get($payload, 'main.humidity', 0),
                    'wind_speed' => round((float) data_get($payload, 'wind.speed', 0), 1),
                ];
            });

            return response()->json($weather);
        } catch (\Throwable) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 502);
        }
    }
}
