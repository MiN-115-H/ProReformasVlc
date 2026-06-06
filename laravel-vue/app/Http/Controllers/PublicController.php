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
                'fotos' => $album->fotos
                    ->filter(fn ($foto) => filled($foto->url))
                    ->map(function ($foto) {
                        return [
                            'id' => $foto->id,
                            'url' => str_starts_with((string) $foto->url, 'http')
                                ? $foto->url
                                : url('/storage/' . ltrim((string) $foto->url, '/')),
                            'descripcion' => $foto->descripcion,
                        ];
                    })
                    ->values(),
            ];
        })->values();

        return response()->json($albumes);
    }
}
