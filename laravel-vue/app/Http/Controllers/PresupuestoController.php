<?php

namespace App\Http\Controllers;

use App\Models\Concepto;
use App\Models\Presupuesto;
use App\Models\TipoPresupuesto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresupuestoController extends Controller
{
    public function conceptos(): JsonResponse
    {
        $tipos = TipoPresupuesto::orderBy('nombre')->get(['id', 'nombre']);

        $conceptos = Concepto::where('activo', true)
            ->orderBy('descripcion')
            ->get(['id', 'descripcion', 'precio_base', 'tipo_presupuesto_id', 'sugerencias']);

        return response()->json([
            'tipos' => $tipos,
            'conceptos' => $conceptos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_nombre' => ['required', 'string', 'max:150'],
            'cliente_telefono' => ['required', 'string', 'max:50'],
            'cliente_email' => ['required', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],
            'fecha_presupuesto' => ['required', 'date'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.descripcion' => ['required', 'string', 'max:150'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'lineas.*.precio' => ['required', 'numeric', 'gte:0'],
            'lineas.*.subtotal' => ['required', 'numeric', 'gte:0'],
            'subtotal' => ['required', 'numeric', 'gte:0'],
            'iva' => ['required', 'numeric', 'gte:0'],
            'total' => ['required', 'numeric', 'gte:0'],
        ]);

        $presupuesto = Presupuesto::create($validated);

        return response()->json([
            'message' => 'Presupuesto guardado correctamente.',
            'id' => $presupuesto->id,
        ], 201);
    }
}
