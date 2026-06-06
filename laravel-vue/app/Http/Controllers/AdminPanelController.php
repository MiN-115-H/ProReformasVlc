<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Concepto;
use App\Models\Contacto;
use App\Models\Presupuesto;
use App\Models\Servicio;
use App\Models\TipoPresupuesto;
use App\Models\Unidad;
use App\Models\User;
use App\Models\Foto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminPanelController extends Controller
{
    public function panelData(): JsonResponse
    {
        $tipos = TipoPresupuesto::orderBy('nombre')->get();
        $unidades = Unidad::orderBy('nombre')->get();

        $conceptos = Concepto::with(['unidad:id,abreviatura', 'tipoPresupuesto:id,nombre'])
            ->orderBy('descripcion')
            ->get()
            ->map(function (Concepto $concepto) {
                return [
                    'id' => $concepto->id,
                    'descripcion' => $concepto->descripcion,
                    'precio_base' => (float) $concepto->precio_base,
                    'unidad_id' => $concepto->unidad_id,
                    'tipo_presupuesto_id' => $concepto->tipo_presupuesto_id,
                    'unidad_abrev' => $concepto->unidad?->abreviatura,
                    'tipo_nombre' => $concepto->tipoPresupuesto?->nombre,
                    'activo' => (bool) $concepto->activo,
                    'created_at' => optional($concepto->created_at)->toIso8601String(),
                ];
            })->values();

        $presupuestos = Presupuesto::with('tipoPresupuesto:id,nombre')
            ->latest('id')
            ->take(50)
            ->get()
            ->map(function (Presupuesto $presupuesto) {
                return [
                    'id' => $presupuesto->id,
                    'titulo' => $presupuesto->titulo,
                    'cliente' => $presupuesto->cliente_nombre,
                    'telefono' => $presupuesto->cliente_telefono,
                    'email' => $presupuesto->cliente_email,
                    'ciudad' => $presupuesto->ciudad,
                    'metros_cuadrados' => $presupuesto->metros_cuadrados,
                    'fecha' => optional($presupuesto->fecha_presupuesto)->format('Y-m-d'),
                    'created_at' => optional($presupuesto->created_at)->toIso8601String(),
                    'tipo_presupuesto_id' => $presupuesto->tipo_presupuesto_id,
                    'tipo' => $presupuesto->tipoPresupuesto?->nombre,
                    'estado' => $presupuesto->estado,
                    'total' => (float) $presupuesto->total,
                ];
            })->values();

        $contactos = Contacto::latest('fecha_recepcion')
            ->latest('id')
            ->take(100)
            ->get()
            ->map(function (Contacto $contacto) {
                return [
                    'id' => $contacto->id,
                    'nombre' => $contacto->nombre,
                    'email' => $contacto->email,
                    'telefono' => $contacto->telefono,
                    'asunto' => $contacto->asunto,
                    'mensaje' => $contacto->mensaje,
                    'leido' => (bool) $contacto->leido,
                    'respondido' => (bool) $contacto->respondido,
                    'fecha_recepcion' => optional($contacto->fecha_recepcion)->toIso8601String(),
                    'created_at' => optional($contacto->created_at)->toIso8601String(),
                ];
            })->values();

        $servicios = Servicio::orderBy('nombre')->get()->map(fn (Servicio $servicio) => $this->mapServicio($servicio))->values();
        $albumes = Album::with('fotos')->orderBy('nombre')->get()->map(function($album) {
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
                            'orden' => $foto->orden,
                        ];
                    })
                    ->values(),
            ];
        })->values();
        $usuarios = User::select('id', 'name', 'email', 'rol', 'activo')->orderBy('name')->get();

        return response()->json([
            'tipos' => $tipos,
            'unidades' => $unidades,
            'conceptos' => $conceptos,
            'presupuestos' => $presupuestos,
            'contactos' => $contactos,
            'servicios' => $servicios,
            'albumes' => $albumes,
            'usuarios' => $usuarios,
        ]);
    }

    public function storeTipo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $tipo = TipoPresupuesto::create($validated);

        return response()->json($tipo, 201);
    }

    public function deleteTipo(TipoPresupuesto $tipo): JsonResponse
    {
        $tipo->delete();
        return response()->json(['message' => 'Tipo eliminado.']);
    }

    public function updateTipo(Request $request, TipoPresupuesto $tipo): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipos_presupuesto', 'nombre')->ignore($tipo->id),
            ],
            'descripcion' => ['nullable', 'string'],
        ]);

        $tipo->update($validated);

        return response()->json($tipo);
    }

    public function storeUnidad(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'abreviatura' => ['required', 'string', 'max:20'],
        ]);

        $unidad = Unidad::create($validated);

        return response()->json($unidad, 201);
    }

    public function deleteUnidad(Unidad $unidad): JsonResponse
    {
        $unidad->delete();
        return response()->json(['message' => 'Unidad eliminada.']);
    }

    public function updateUnidad(Request $request, Unidad $unidad): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'abreviatura' => [
                'required',
                'string',
                'max:20',
                Rule::unique('unidades', 'abreviatura')->ignore($unidad->id),
            ],
        ]);

        $unidad->update($validated);

        return response()->json($unidad);
    }

    public function storeConcepto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:150'],
            'precio_base' => ['required', 'numeric', 'gte:0'],
            'unidad_id' => ['required', 'exists:unidades,id'],
            'tipo_presupuesto_id' => ['required', 'exists:tipos_presupuesto,id'],
        ]);

        $concepto = Concepto::create($validated + ['activo' => true]);

        return response()->json($concepto, 201);
    }

    public function updateConcepto(Request $request, Concepto $concepto): JsonResponse
    {
        $validated = $request->validate([
            'descripcion' => ['sometimes', 'required', 'string', 'max:150'],
            'precio_base' => ['sometimes', 'required', 'numeric', 'gte:0'],
            'unidad_id' => ['sometimes', 'required', 'exists:unidades,id'],
            'tipo_presupuesto_id' => ['sometimes', 'required', 'exists:tipos_presupuesto,id'],
            'activo' => ['sometimes', 'required', 'boolean'],
        ]);

        if ($validated === []) {
            return response()->json(['message' => 'No se enviaron cambios.'], 422);
        }

        $concepto->update($validated);

        $concepto->load(['unidad:id,abreviatura', 'tipoPresupuesto:id,nombre']);

        return response()->json([
            'id' => $concepto->id,
            'descripcion' => $concepto->descripcion,
            'precio_base' => (float) $concepto->precio_base,
            'unidad_id' => $concepto->unidad_id,
            'tipo_presupuesto_id' => $concepto->tipo_presupuesto_id,
            'unidad_abrev' => $concepto->unidad?->abreviatura,
            'tipo_nombre' => $concepto->tipoPresupuesto?->nombre,
            'activo' => (bool) $concepto->activo,
        ]);
    }

    public function deleteConcepto(Concepto $concepto): JsonResponse
    {
        $concepto->delete();
        return response()->json(['message' => 'Concepto eliminado.']);
    }

    public function storePresupuesto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente' => ['required', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'tipo_presupuesto_id' => ['nullable', 'exists:tipos_presupuesto,id'],
        ]);

        $presupuesto = Presupuesto::create([
            'cliente_nombre' => $validated['cliente'],
            'cliente_telefono' => 'N/D',
            'cliente_email' => 'pendiente+'.time().'@proreformas.local',
            'direccion' => null,
            'ciudad' => $validated['ciudad'] ?? null,
            'observaciones' => null,
            'fecha_presupuesto' => now()->toDateString(),
            'lineas' => [],
            'subtotal' => 0,
            'iva' => 0,
            'total' => 0,
            'estado' => 'pendiente',
            'tipo_presupuesto_id' => $validated['tipo_presupuesto_id'] ?? null,
        ]);

        return response()->json($presupuesto, 201);
    }

    public function storeServicio(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:width=1200,height=800'],
        ]);

        $imagePath = $request->file('imagen')->store('servicios', 'public');

        $servicio = Servicio::create($validated + [
            'precio_base' => null,
            'imagen_portada' => $imagePath,
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        return response()->json($this->mapServicio($servicio), 201);
    }

    public function storeAlbum(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['nullable', 'string', 'max:50'],
        ]);

        $album = Album::create($validated + ['fecha_creacion' => now()]);
        $album->load('fotos');

        return response()->json([
            'id' => $album->id,
            'nombre' => $album->nombre,
            'descripcion' => $album->descripcion,
            'categoria' => $album->categoria,
            'fotos' => [],
        ], 201);
    }

    public function updateServicio(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:width=1200,height=800'],
        ]);

        if ($request->hasFile('imagen')) {
            if ($servicio->imagen_portada) {
                Storage::disk('public')->delete($servicio->imagen_portada);
            }

            $validated['imagen_portada'] = $request->file('imagen')->store('servicios', 'public');
        }

        $servicio->update($validated);

        return response()->json($this->mapServicio($servicio));
    }

    public function deleteServicio(Servicio $servicio): JsonResponse
    {
        if ($servicio->imagen_portada) {
            Storage::disk('public')->delete($servicio->imagen_portada);
        }

        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado.']);
    }

    public function updateAlbum(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['nullable', 'string', 'max:50'],
        ]);

        $album->update($validated);
        $album->load('fotos');

        return response()->json([
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
                        'orden' => $foto->orden,
                    ];
                })
                ->values(),
        ]);
    }

    public function deleteAlbum(Album $album): JsonResponse
    {
        foreach($album->fotos as $foto) {
            if (filled($foto->url) && !str_starts_with((string) $foto->url, 'http')) {
                Storage::disk('public')->delete($foto->url);
            }
            $foto->delete();
        }
        $album->delete();
        return response()->json(['message' => 'Album eliminado.']);
    }

    public function storeFoto(Request $request, Album $album): JsonResponse
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('imagen')->store('albums', 'public');
        $orden = $album->fotos()->max('orden') + 1;

        $foto = $album->fotos()->create([
            'url' => $path,
            'orden' => $orden,
            'fecha_subida' => now()
        ]);

        return response()->json([
            'id' => $foto->id,
            'url' => url('/storage/' . $path),
            'descripcion' => $foto->descripcion,
            'orden' => $foto->orden,
        ], 201);
    }

    public function deleteFoto(Foto $foto): JsonResponse
    {
        if (filled($foto->url) && !str_starts_with((string) $foto->url, 'http')) {
            Storage::disk('public')->delete($foto->url);
        }
        $foto->delete();
        return response()->json(['message' => 'Foto eliminada.']);
    }

    public function toggleUsuario(User $usuario): JsonResponse
    {
        $usuario->update(['activo' => ! $usuario->activo]);
        return response()->json(['activo' => (bool) $usuario->activo]);
    }

    public function deleteUsuario(User $usuario): JsonResponse
    {
        if ($usuario->rol === 'admin') {
            return response()->json(['message' => 'No se puede eliminar al administrador principal.'], 422);
        }

        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado.']);
    }

    public function updateUsuario(Request $request, User $usuario): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($usuario->id),
            ],
        ]);

        $usuario->update($validated);

        return response()->json([
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'rol' => $usuario->rol,
            'activo' => (bool) $usuario->activo,
        ]);
    }

    public function updateEstadoPresupuesto(Request $request, Presupuesto $presupuesto): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:pendiente,aceptado,rechazado'],
        ]);

        $presupuesto->update($validated);

        return response()->json(['estado' => $presupuesto->estado]);
    }

    public function updateEstadoContacto(Request $request, Contacto $contacto): JsonResponse
    {
        $validated = $request->validate([
            'leido' => ['sometimes', 'boolean'],
            'respondido' => ['sometimes', 'boolean'],
        ]);

        if ($validated === []) {
            return response()->json(['message' => 'No se enviaron cambios.'], 422);
        }

        $contacto->update($validated);

        return response()->json([
            'leido' => (bool) $contacto->leido,
            'respondido' => (bool) $contacto->respondido,
        ]);
    }

    public function deleteContacto(Contacto $contacto): JsonResponse
    {
        $contacto->delete();
        return response()->json(['message' => 'Contacto eliminado.']);
    }

    public function showPresupuesto(Presupuesto $presupuesto): JsonResponse
    {
        $presupuesto->load('tipoPresupuesto:id,nombre');

        return response()->json([
            'id' => $presupuesto->id,
            'titulo' => $presupuesto->titulo,
            'fecha' => optional($presupuesto->fecha_presupuesto)->format('Y-m-d'),
            'cliente_nombre' => $presupuesto->cliente_nombre,
            'cliente_telefono' => $presupuesto->cliente_telefono,
            'cliente_email' => $presupuesto->cliente_email,
            'direccion' => $presupuesto->direccion,
            'ciudad' => $presupuesto->ciudad,
            'observaciones' => $presupuesto->observaciones,
            'tipo' => $presupuesto->tipoPresupuesto?->nombre,
            'metros_cuadrados' => $presupuesto->metros_cuadrados,
            'estado' => $presupuesto->estado,
            'lineas' => $presupuesto->lineas ?? [],
            'subtotal' => (float) $presupuesto->subtotal,
            'iva' => (float) $presupuesto->iva,
            'total' => (float) $presupuesto->total,
        ]);
    }

    public function storeUsuario(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
        ]);

        $usuario = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('Cambio1234!'),
            'rol' => 'editor',
            'activo' => true,
        ]);

        return response()->json($usuario, 201);
    }

    private function mapServicio(Servicio $servicio): array
    {
        return [
            'id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'descripcion' => $servicio->descripcion,
            'imagen_portada' => $servicio->imagen_portada,
            'imagen_url' => filled($servicio->imagen_portada)
                ? url('/storage/' . ltrim((string) $servicio->imagen_portada, '/'))
                : null,
            'activo' => (bool) $servicio->activo,
            'fecha_creacion' => optional($servicio->fecha_creacion)->toIso8601String(),
            'created_at' => optional($servicio->created_at)->toIso8601String(),
        ];
    }
}
