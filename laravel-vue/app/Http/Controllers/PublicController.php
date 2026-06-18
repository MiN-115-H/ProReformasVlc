<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PublicController extends Controller
{
    public function albums(): JsonResponse
    {
        $albumes = Album::with(['fotos' => function ($query) {
            $query->orderBy('orden');
        }])->orderBy('nombre')->get()->map(function($album) {
            return [
                'id' => $album->id,
                'nombre' => $album->nombre,
                'descripcion' => $album->descripcion,
                'categoria' => $album->categoria,
                'fotos' => $album->fotos->map(function($foto) {
                    return [
                        'id' => $foto->id,
                        'url' => $this->toPublicFotoUrl($foto->url),
                        'descripcion' => $foto->descripcion,
                    ];
                })
            ];
        })->values();

        return response()->json($albumes);
    }

    private function toPublicFotoUrl(string $url): string
    {
        // Absolute URL (http/https): extract just the path to avoid mixed-content issues
        if (str_starts_with($url, 'http')) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            $normalized = ltrim($path, '/');
            if (str_starts_with($normalized, 'storage/')) {
                return '/' . $normalized;
            }
            return '/storage/' . $normalized;
        }

        $normalized = ltrim($url, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return '/' . $normalized;
        }

        return '/storage/' . $normalized;
    }
}
