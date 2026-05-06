<?php

namespace Tests\Feature;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiciosFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_services_endpoint_returns_active_services(): void
    {
        Storage::fake('public');

        Servicio::create([
            'nombre' => 'Electricidad',
            'descripcion' => 'Instalaciones completas.',
            'imagen_portada' => 'servicios/electricidad.webp',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        Servicio::create([
            'nombre' => 'Oculto',
            'descripcion' => 'No debe salir.',
            'imagen_portada' => 'servicios/oculto.webp',
            'activo' => false,
            'fecha_creacion' => now(),
        ]);

        $response = $this->getJson('/api/servicios');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'servicios')
            ->assertJsonPath('servicios.0.titulo', 'Electricidad')
            ->assertJsonPath('servicios.0.desc', 'Instalaciones completas.')
            ->assertJsonPath('servicios.0.img', '/storage/servicios/electricidad.webp');
    }

    public function test_admin_service_creation_requires_exact_card_image_dimensions(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'rol' => 'admin',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->post('/api/admin/servicios', [
            'nombre' => 'Pintura',
            'descripcion' => 'Acabados interiores.',
            'imagen' => UploadedFile::fake()->image('pintura.jpg', 1000, 800),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['imagen']);
    }
}
