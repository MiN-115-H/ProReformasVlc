<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublicController extends Controller
{
    public function albums(): JsonResponse
    {
        try {
            $albumes = Album::with(['fotos' => function ($query) {
                $query->orderBy('orden');
            }])->orderBy('nombre')->get()->map(function ($album) {
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

            return response()->json(
                $albumes,
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'No se pudieron cargar los albums.',
                'error' => $e->getMessage(),
                'exception' => class_basename($e),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
}
