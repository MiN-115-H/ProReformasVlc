<?php

namespace Database\Seeders;

use App\Models\Concepto;
use App\Models\TipoPresupuesto;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

class ConceptosCocinaYBanoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que la unidad "Unidad" existe
        $unidad = Unidad::firstOrCreate(
            ['abreviatura' => 'ud'],
            ['nombre' => 'Unidad', 'abreviatura' => 'ud']
        );

        // Asegurar tipos de presupuesto
        $tipoCocina = TipoPresupuesto::firstOrCreate(
            ['nombre' => 'Cocina'],
            ['nombre' => 'Cocina', 'descripcion' => 'Reforma de cocina']
        );

        $tipoBano = TipoPresupuesto::firstOrCreate(
            ['nombre' => 'Baño'],
            ['nombre' => 'Baño', 'descripcion' => 'Reforma de baño']
        );

        // ── COCINA ────────────────────────────────────────────────────────────
        $conceptosCocina = [
            'PICADO DE PAREDES, DESESCOMBRO Y ALICATADO',
            'COLOCACION DE PAVIMENTO ARRANCANDO EL EXISTENTE',
            'DERRIBO DE ESCAYOLA',
            'COLOCACION DE ESCAYOLA',
            'PINTURA TECHO COCINA',
            'INSTALACION DE FONTANERIA',
            'INSTALACION DE ELECTRICIDAD',
            'INSTALACION DE GAS',
            'MODIFICAR CONTADOR DE GAS',
            'AYUDAS A FONTANERO Y ELECTRICISTA',
            'VENTANA',
            'FALCADO DE VENTANA',
            'MOBILIARIO DE COCINA SEGÚN COMPOSICION',
            'BANCADA',
            'APLACADO',
            'FREGADERO',
            'GRIFO',
            'CAMPANA',
            'PLACA',
            'HORNO',
            'MICROONDAS',
            'LAVAVAJILLAS',
            'LAVADORA',
            'ILUMINACION',
            'FRIGORIFICO',
            'OTRAS',
        ];

        $idsCocina = [];
        foreach ($conceptosCocina as $desc) {
            $c = Concepto::firstOrCreate(
                ['descripcion' => $desc, 'tipo_presupuesto_id' => $tipoCocina->id],
                [
                    'precio_base'         => 0.00,
                    'unidad_id'           => $unidad->id,
                    'tipo_presupuesto_id' => $tipoCocina->id,
                    'activo'              => true,
                ]
            );
            $idsCocina[$desc] = $c->id;
        }

        // Sugerencias Cocina
        $sugerenciasCocina = [
            'DERRIBO DE ESCAYOLA' => ['COLOCACION DE ESCAYOLA'],
            'VENTANA' => [
                'FALCADO DE VENTANA',
                'PICADO DE PAREDES, DESESCOMBRO Y ALICATADO',
            ],
            'MOBILIARIO DE COCINA SEGÚN COMPOSICION' => [
                'BANCADA',
                'APLACADO',
                'FREGADERO',
                'GRIFO',
            ],
            'FREGADERO' => [
                'GRIFO',
                'INSTALACION DE FONTANERIA',
            ],
            'PLACA' => [
                'INSTALACION DE GAS',
                'CAMPANA',
            ],
            'CAMPANA' => ['INSTALACION DE ELECTRICIDAD'],
            'HORNO' => ['INSTALACION DE ELECTRICIDAD'],
            'MICROONDAS' => ['INSTALACION DE ELECTRICIDAD'],
            'LAVAVAJILLAS' => [
                'INSTALACION DE FONTANERIA',
                'INSTALACION DE ELECTRICIDAD',
            ],
            'LAVADORA' => [
                'INSTALACION DE FONTANERIA',
                'INSTALACION DE ELECTRICIDAD',
            ],
            'ILUMINACION' => ['INSTALACION DE ELECTRICIDAD'],
        ];

        foreach ($sugerenciasCocina as $desc => $extras) {
            if (!isset($idsCocina[$desc])) continue;
            $sugerenciaIds = array_values(array_filter(
                array_map(fn ($e) => $idsCocina[$e] ?? null, $extras)
            ));
            Concepto::where('id', $idsCocina[$desc])->update(['sugerencias' => json_encode($sugerenciaIds)]);
        }

        // ── BAÑO ──────────────────────────────────────────────────────────────
        $conceptosBano = [
            'PICADO DE PAREDES, DESESCOMBRO Y ALICATADO',
            'COLOCACION DE PAVIMENTO ARRANCANDO EL EXISTENTE',
            'DERRIBO DE ESCAYOLA',
            'COLOCACION DE ESCAYOLA',
            'PINTURA TECHO BAÑO',
            'INSTALACION DE FONTANERIA',
            'INSTALACION DE ELECTRICIDAD',
            'AYUDAS A FONTANERO Y ELECTRICISTA',
            'PLATO DUCHA CARGAS MINERALES',
            'COLUMNA DUCHA Y MONTAJE',
            'ESTANTES DUCHA',
            'MAMPARA',
            'INSTALACION MAMPARA',
            'MUEBLE BAÑO',
            'GRIFO LAVABO',
            'INODORO GAP Y MONTAJE',
            'BIDE SIN TAPA',
            'GRIFO BIDE',
            'DOWNLINE DE LED',
        ];

        $idsBano = [];
        foreach ($conceptosBano as $desc) {
            $c = Concepto::firstOrCreate(
                ['descripcion' => $desc, 'tipo_presupuesto_id' => $tipoBano->id],
                [
                    'precio_base'         => 0.00,
                    'unidad_id'           => $unidad->id,
                    'tipo_presupuesto_id' => $tipoBano->id,
                    'activo'              => true,
                ]
            );
            $idsBano[$desc] = $c->id;
        }

        // Sugerencias Baño
        $sugerenciasBano = [
            'DERRIBO DE ESCAYOLA' => ['COLOCACION DE ESCAYOLA'],
            'PLATO DUCHA CARGAS MINERALES' => [
                'COLUMNA DUCHA Y MONTAJE',
                'MAMPARA',
                'INSTALACION MAMPARA',
                'ESTANTES DUCHA',
            ],
            'MAMPARA' => ['INSTALACION MAMPARA'],
            'MUEBLE BAÑO' => ['GRIFO LAVABO'],
            'BIDE SIN TAPA' => ['GRIFO BIDE'],
            'DOWNLINE DE LED' => ['INSTALACION DE ELECTRICIDAD'],
        ];

        foreach ($sugerenciasBano as $desc => $extras) {
            if (!isset($idsBano[$desc])) continue;
            $sugerenciaIds = array_values(array_filter(
                array_map(fn ($e) => $idsBano[$e] ?? null, $extras)
            ));
            Concepto::where('id', $idsBano[$desc])->update(['sugerencias' => json_encode($sugerenciaIds)]);
        }
    }
}
