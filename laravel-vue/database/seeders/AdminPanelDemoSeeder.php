<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Articulo;
use App\Models\Concepto;
use App\Models\Contacto;
use App\Models\Foto;
use App\Models\Presupuesto;
use App\Models\Servicio;
use App\Models\TipoPresupuesto;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminPanelDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@proreformasvlc.com'],
            [
                'name' => 'Admin ProReformas',
                'password' => Hash::make('Admin12345!'),
                'rol' => 'admin',
                'activo' => true,
            ]
        );

        $tipoCocina = TipoPresupuesto::updateOrCreate(['nombre' => 'Cocina'], ['descripcion' => 'Reformas de cocina completas']);
        $tipoBano = TipoPresupuesto::updateOrCreate(['nombre' => 'Bano'], ['descripcion' => 'Reformas y rediseno de banos']);
        $tipoIntegral = TipoPresupuesto::updateOrCreate(['nombre' => 'Integral'], ['descripcion' => 'Reformas integrales de vivienda']);

        $unidadM2 = Unidad::updateOrCreate(['abreviatura' => 'm2'], ['nombre' => 'Metro cuadrado']);
        $unidadUd = Unidad::updateOrCreate(['abreviatura' => 'ud'], ['nombre' => 'Unidad']);
        $unidadHora = Unidad::updateOrCreate(['abreviatura' => 'h'], ['nombre' => 'Hora']);

        Concepto::updateOrCreate(
            ['descripcion' => 'Alicatado cocina'],
            ['precio_base' => 44.00, 'unidad_id' => $unidadM2->id, 'tipo_presupuesto_id' => $tipoCocina->id, 'activo' => true]
        );
        Concepto::updateOrCreate(
            ['descripcion' => 'Cambio de banera por ducha'],
            ['precio_base' => 890.00, 'unidad_id' => $unidadUd->id, 'tipo_presupuesto_id' => $tipoBano->id, 'activo' => true]
        );
        Concepto::updateOrCreate(
            ['descripcion' => 'Mano de obra oficial'],
            ['precio_base' => 35.00, 'unidad_id' => $unidadHora->id, 'tipo_presupuesto_id' => $tipoIntegral->id, 'activo' => true]
        );

        $serviciosData = [
            ['nombre' => 'Albanileria', 'descripcion' => 'Tabiques, alicatados, pavimentos y molduras', 'precio_base' => 38.00],
            ['nombre' => 'Carpinteria', 'descripcion' => 'Puertas, armarios, tarimas y muebles a medida', 'precio_base' => 42.00],
            ['nombre' => 'Fontaneria', 'descripcion' => 'Instalaciones multicapa y bajantes comunitarias', 'precio_base' => 40.00],
            ['nombre' => 'Electricidad', 'descripcion' => 'Cuadros, iluminacion y acometidas', 'precio_base' => 37.00],
            ['nombre' => 'Pintura', 'descripcion' => 'Alisados, estucos y acabados decorativos', 'precio_base' => 16.00],
            ['nombre' => 'Aire acondicionado', 'descripcion' => 'Instalacion de maquinaria y conductos', 'precio_base' => 58.00],
        ];

        $servicios = collect($serviciosData)->map(function ($servicio) {
            return Servicio::updateOrCreate(
                ['nombre' => $servicio['nombre']],
                [
                    'descripcion' => $servicio['descripcion'],
                    'precio_base' => $servicio['precio_base'],
                    'activo' => true,
                    'fecha_creacion' => now(),
                ]
            );
        });

        $articulo1 = Articulo::updateOrCreate(
            ['nombre' => 'Kit alicatado premium'],
            [
                'servicio_id' => $servicios->firstWhere('nombre', 'Albanileria')->id,
                'descripcion' => 'Materiales ceramicos + adhesivos de alta gama',
                'precio' => 520.00,
                'activo' => true,
                'fecha_creacion' => now(),
            ]
        );

        $articulo2 = Articulo::updateOrCreate(
            ['nombre' => 'Pack banera a ducha'],
            [
                'servicio_id' => $servicios->firstWhere('nombre', 'Fontaneria')->id,
                'descripcion' => 'Plato de ducha, mampara y griferia',
                'precio' => 980.00,
                'activo' => true,
                'fecha_creacion' => now(),
            ]
        );

        $albumCocinas = Album::updateOrCreate(
            ['nombre' => 'Cocinas 2026'],
            ['descripcion' => 'Reformas recientes de cocinas', 'fecha_creacion' => now()]
        );
        $albumBanos = Album::updateOrCreate(
            ['nombre' => 'Banos 2026'],
            ['descripcion' => 'Antes y despues de banos', 'fecha_creacion' => now()]
        );

        Foto::updateOrCreate(
            ['album_id' => $albumCocinas->id, 'orden' => 1],
            ['url' => 'https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?auto=format&fit=crop&w=1200&q=80', 'descripcion' => 'Cocina abierta con isla', 'fecha_subida' => now()]
        );
        Foto::updateOrCreate(
            ['album_id' => $albumCocinas->id, 'orden' => 2],
            ['url' => 'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?auto=format&fit=crop&w=1200&q=80', 'descripcion' => 'Cocina minimalista', 'fecha_subida' => now()]
        );
        Foto::updateOrCreate(
            ['album_id' => $albumBanos->id, 'orden' => 1],
            ['url' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=1200&q=80', 'descripcion' => 'Bano compacto optimizado', 'fecha_subida' => now()]
        );

        Contacto::updateOrCreate(
            ['email' => 'maria.gomez@email.com', 'asunto' => 'Reforma cocina en Benimaclet'],
            ['nombre' => 'Maria Gomez', 'telefono' => '620123456', 'mensaje' => 'Quiero presupuesto para renovar cocina de 12 m2.', 'leido' => false, 'respondido' => false, 'fecha_recepcion' => now()->subDays(2)]
        );
        Contacto::updateOrCreate(
            ['email' => 'javier.ruiz@email.com', 'asunto' => 'Cambio de banera'],
            ['nombre' => 'Javier Ruiz', 'telefono' => '645987321', 'mensaje' => 'Necesito cambiar banera por plato de ducha en Valencia.', 'leido' => true, 'respondido' => false, 'fecha_recepcion' => now()->subDay()]
        );

        $presupuesto1 = Presupuesto::updateOrCreate(
            ['cliente_email' => 'laura.martin@email.com'],
            [
                'cliente_nombre' => 'Laura Martin',
                'cliente_telefono' => '611223344',
                'direccion' => 'Calle Quart 15',
                'ciudad' => 'Valencia',
                'observaciones' => 'Obra con plazo de 3 semanas',
                'fecha_presupuesto' => now()->toDateString(),
                'lineas' => [
                    ['descripcion' => 'Alicatado cocina', 'cantidad' => 16, 'precio' => 44, 'subtotal' => 704],
                    ['descripcion' => 'Mano de obra oficial', 'cantidad' => 20, 'precio' => 35, 'subtotal' => 700],
                ],
                'subtotal' => 1404,
                'iva' => 294.84,
                'total' => 1698.84,
                'estado' => 'pendiente',
                'tipo_presupuesto_id' => $tipoCocina->id,
            ]
        );

        $presupuesto2 = Presupuesto::updateOrCreate(
            ['cliente_email' => 'antonio.perez@email.com'],
            [
                'cliente_nombre' => 'Antonio Perez',
                'cliente_telefono' => '600998877',
                'direccion' => 'Av. del Cid 101',
                'ciudad' => 'Valencia',
                'observaciones' => 'Incluye cambio de fontaneria',
                'fecha_presupuesto' => now()->subDay()->toDateString(),
                'lineas' => [
                    ['descripcion' => 'Cambio de banera por ducha', 'cantidad' => 1, 'precio' => 890, 'subtotal' => 890],
                ],
                'subtotal' => 890,
                'iva' => 186.90,
                'total' => 1076.90,
                'estado' => 'aceptado',
                'tipo_presupuesto_id' => $tipoBano->id,
            ]
        );

        DB::table('presupuesto_detalles')->updateOrInsert(
            ['presupuesto_id' => $presupuesto1->id, 'articulo_id' => $articulo1->id],
            ['cantidad' => 1, 'precio_unitario' => 520, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('presupuesto_detalles')->updateOrInsert(
            ['presupuesto_id' => $presupuesto2->id, 'articulo_id' => $articulo2->id],
            ['cantidad' => 1, 'precio_unitario' => 980, 'created_at' => now(), 'updated_at' => now()]
        );

        User::updateOrCreate(
            ['email' => 'editor.demo@proreformasvlc.com'],
            [
                'name' => 'Editor Demo',
                'password' => Hash::make('Cambio1234!'),
                'rol' => 'editor',
                'activo' => true,
            ]
        );
    }
}
