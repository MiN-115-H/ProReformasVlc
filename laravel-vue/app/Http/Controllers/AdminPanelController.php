<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Concepto;
use App\Models\Presupuesto;
use App\Models\Servicio;
use App\Models\TipoPresupuesto;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                ];
            })->values();

        $presupuestos = Presupuesto::with('tipoPresupuesto:id,nombre')
            ->latest('id')
            ->take(50)
            ->get()
            ->map(function (Presupuesto $presupuesto) {
                return [
                    'id' => $presupuesto->id,
                    'cliente' => $presupuesto->cliente_nombre,
                    'ciudad' => $presupuesto->ciudad,
                    'tipo' => $presupuesto->tipoPresupuesto?->nombre,
                    'estado' => $presupuesto->estado,
                    'total' => (float) $presupuesto->total,
                ];
            })->values();

        $servicios = Servicio::orderBy('nombre')->get();
        $albumes = Album::orderBy('nombre')->get();
        $usuarios = User::select('id', 'name', 'email', 'rol', 'activo')->orderBy('name')->get();

        return response()->json([
            'tipos' => $tipos,
            'unidades' => $unidades,
            'conceptos' => $conceptos,
            'presupuestos' => $presupuestos,
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
        ]);

        $servicio = Servicio::create($validated + [
            'precio_base' => null,
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        return response()->json($servicio, 201);
    }

    public function storeAlbum(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $album = Album::create($validated + ['fecha_creacion' => now()]);

        return response()->json($album, 201);
    }

    public function updateServicio(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $servicio->update($validated);

        return response()->json($servicio);
    }

    public function deleteServicio(Servicio $servicio): JsonResponse
    {
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado.']);
    }

    public function updateAlbum(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $album->update($validated);

        return response()->json($album);
    }

    public function deleteAlbum(Album $album): JsonResponse
    {
        $album->delete();
        return response()->json(['message' => 'Album eliminado.']);
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
}
