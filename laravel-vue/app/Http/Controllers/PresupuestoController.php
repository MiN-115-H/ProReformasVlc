<?php

namespace App\Http\Controllers;

use App\Models\Concepto;
use App\Models\Contacto;
use App\Models\Presupuesto;
use App\Models\Servicio;
use App\Models\TipoPresupuesto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'titulo' => ['required', 'string', 'max:180'],
            'metros_cuadrados' => ['required', 'numeric', 'gt:0'],
            'tipo_presupuesto_id' => ['required', 'exists:tipos_presupuesto,id'],
            'cliente_nombre' => ['required', 'string', 'min:3', 'max:150', 'regex:/^[\pL\s\-\']+$/u'],
            'cliente_telefono' => ['required', 'string', 'max:50', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'cliente_email' => ['required', 'email', 'max:150'],
            'direccion' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:120', 'regex:/^[\pL\s\-\']+$/u'],
            'observaciones' => ['nullable', 'string'],
            'fecha_presupuesto' => ['required', 'date'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.descripcion' => ['required', 'string', 'max:150'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'lineas.*.precio' => ['required', 'numeric', 'gte:0'],
            'lineas.*.subtotal' => ['required', 'numeric', 'gte:0'],
            'lineas.*.categoria_id' => ['required', 'integer', 'exists:tipos_presupuesto,id'],
            'subtotal' => ['required', 'numeric', 'gte:0'],
            'iva' => ['required', 'numeric', 'gte:0'],
            'total' => ['required', 'numeric', 'gte:0'],
        ]);

        $categoriasLineas = collect($validated['lineas'])
            ->pluck('categoria_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoriasLineas->count() !== 1 || $categoriasLineas->first() !== (int) $validated['tipo_presupuesto_id']) {
            throw ValidationException::withMessages([
                'lineas' => ['Las líneas no pueden mezclar categorías distintas al tipo de presupuesto seleccionado.'],
            ]);
        }

        $presupuesto = Presupuesto::create($validated);

        return response()->json([
            'message' => 'Presupuesto guardado correctamente.',
            'id' => $presupuesto->id,
        ], 201);
    }

    public function servicios(): JsonResponse
    {
        $servicios = Servicio::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Servicio $servicio) {
                return [
                    'id' => $servicio->id,
                    'titulo' => $servicio->nombre,
                    'desc' => $servicio->descripcion,
                    'img' => $servicio->imagen_portada ? Storage::disk('public')->url($servicio->imagen_portada) : null,
                ];
            })
            ->values();

        return response()->json([
            'servicios' => $servicios,
        ]);
    }

    public function storeContacto(Request $request): JsonResponse
    {
        $recaptchaEnabled = !empty(config('services.recaptcha.site_key')) && !empty(config('services.recaptcha.secret_key'));

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s\-\']+$/u'],
            'email' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'asunto' => ['nullable', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'min:10', 'max:4000'],
            'website' => ['nullable', 'string', 'max:0'],
            'recaptcha_token' => ['nullable', 'string'],
        ]);

        if (!empty($validated['website'] ?? '')) {
            return response()->json(['message' => 'Solicitud no válida.'], 422);
        }

        if ($recaptchaEnabled && !$this->verifyRecaptchaToken($validated['recaptcha_token'] ?? null)) {
            return response()->json(['message' => 'No se pudo verificar la seguridad del formulario. Inténtalo de nuevo.'], 422);
        }

        $contacto = Contacto::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'asunto' => $validated['asunto'] ?? 'Consulta web',
            'mensaje' => $validated['mensaje'],
            'leido' => false,
            'respondido' => false,
            'fecha_recepcion' => now(),
        ]);

        return response()->json([
            'message' => 'Formulario enviado correctamente.',
            'id' => $contacto->id,
        ], 201);
    }

    private function verifyRecaptchaToken(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        $secretKey = (string) config('services.recaptcha.secret_key');
        $expectedAction = (string) config('services.recaptcha.action', 'contact_submit');
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        if ($secretKey === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                ]);

            $data = $response->json();

            if (!$response->ok() || !is_array($data) || !($data['success'] ?? false)) {
                return false;
            }

            $action = (string) ($data['action'] ?? '');
            $score = (float) ($data['score'] ?? 0);

            return $action === $expectedAction && $score >= $minScore;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
