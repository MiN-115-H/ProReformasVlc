<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiciosCatalogoSeeder extends Seeder
{
    /**
     * @var array<int, array{nombre:string, descripcion:string, palette:array<int, string>, legacy:array<int, string>}>
     */
    private array $servicios = [
        [
            'nombre' => 'Albañilería',
            'descripcion' => 'Tabiques, alicatados, pavimentos, maestreado de yeso y barrecha, escayolas, molduras.',
            'palette' => ['#355c7d', '#6c5b7b', '#c06c84'],
            'legacy' => ['Albanileria'],
        ],
        [
            'nombre' => 'Carpintería',
            'descripcion' => 'Puertas, armarios empotrados, suelos, tarimas, parquet, muebles a medida.',
            'palette' => ['#7b4f2c', '#b08968', '#ddb892'],
            'legacy' => ['Carpinteria'],
        ],
        [
            'nombre' => 'Fontanería',
            'descripcion' => 'Bajantes comunitarias, instalaciones multicapa, polipropileno, tuberías Wirsbo.',
            'palette' => ['#005f73', '#0a9396', '#94d2bd'],
            'legacy' => ['Fontaneria'],
        ],
        [
            'nombre' => 'Electricidad',
            'descripcion' => 'Instalaciones, boletines, cuadros automáticos, iluminación, acometidas.',
            'palette' => ['#22223b', '#4a4e69', '#f2e9e4'],
            'legacy' => [],
        ],
        [
            'nombre' => 'Pintura',
            'descripcion' => 'Esponjados, alisados, estuco veneciano, tierras florentinas, gotelé.',
            'palette' => ['#9a031e', '#fb8b24', '#ffb703'],
            'legacy' => [],
        ],
        [
            'nombre' => 'Gas y calefacción',
            'descripcion' => 'Instalaciones, boletines, calderas, calentadores, radiadores, toalleros.',
            'palette' => ['#7f5539', '#b08968', '#e6ccb2'],
            'legacy' => ['Gas y Calefacción'],
        ],
        [
            'nombre' => 'Aire acondicionado',
            'descripcion' => 'Instalaciones, maquinaria, conductos, hogar, comercial e industrial.',
            'palette' => ['#264653', '#2a9d8f', '#e9f5f2'],
            'legacy' => [],
        ],
        [
            'nombre' => 'Cristalería',
            'descripcion' => 'Mamparas, ventanales, correderas, abatibles, oscilobatientes, lucernarios.',
            'palette' => ['#457b9d', '#a8dadc', '#f1faee'],
            'legacy' => ['Cristaleria'],
        ],
        [
            'nombre' => 'Persianas',
            'descripcion' => 'Enrollables, pvc, aluminio, motorizadas, de seguridad, mosquiteras.',
            'palette' => ['#3d405b', '#81b29a', '#f4f1de'],
            'legacy' => [],
        ],
        [
            'nombre' => 'Cerrajería',
            'descripcion' => 'Puertas, ventanas, rejas fijas, abatibles, extensibles, barandillas, decoraciones.',
            'palette' => ['#1d3557', '#457b9d', '#a8dadc'],
            'legacy' => ['Cerrajeria'],
        ],
        [
            'nombre' => 'Acero inoxidable',
            'descripcion' => 'Barandillas, puertas, ventanas, perfilería, trabajos a medida.',
            'palette' => ['#495057', '#adb5bd', '#dee2e6'],
            'legacy' => ['Acero Inoxidable'],
        ],
        [
            'nombre' => 'Electrodomésticos',
            'descripcion' => 'Hornos, microondas, encimeras, lavadoras, secadoras, neveras, lavavajillas, campanas.',
            'palette' => ['#1b4332', '#40916c', '#d8f3dc'],
            'legacy' => ['Electrodomesticos'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->servicios as $index => $data) {
            $path = $this->storeCover($data, $index);
            $servicio = $this->findExistingServicio($data['nombre'], $data['legacy']);

            if (! $servicio) {
                $servicio = new Servicio();
                $servicio->fecha_creacion = now();
            }

            $servicio->fill([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'precio_base' => null,
                'imagen_portada' => $path,
                'activo' => true,
            ]);

            if (! $servicio->fecha_creacion) {
                $servicio->fecha_creacion = now();
            }

            $servicio->save();
        }
    }

    /**
     * @param array<int, string> $legacy
     */
    private function findExistingServicio(string $nombre, array $legacy): ?Servicio
    {
        return Servicio::query()
            ->where(function ($query) use ($nombre, $legacy) {
                $query->where('nombre', $nombre);

                foreach ($legacy as $legacyName) {
                    $query->orWhere('nombre', $legacyName);
                }
            })
            ->first();
    }

    /**
     * @param array{nombre:string, descripcion:string, palette:array<int, string>} $data
     */
    private function storeCover(array $data, int $index): string
    {
        $filename = 'servicios/'.Str::slug($data['nombre']).'.svg';
        Storage::disk('public')->put($filename, $this->buildSvg($data, $index));

        return $filename;
    }

    /**
     * @param array{nombre:string, descripcion:string, palette:array<int, string>} $data
     */
    private function buildSvg(array $data, int $index): string
    {
        [$primary, $secondary, $accent] = $data['palette'];
        $title = e($data['nombre']);
        $description = e($data['descripcion']);
        $badge = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $lines = $this->wrapText($data['descripcion'], 38);

        $textBlocks = '';
        foreach ($lines as $lineIndex => $line) {
            $y = 470 + ($lineIndex * 42);
            $safeLine = e($line);
            $textBlocks .= "<text x=\"86\" y=\"{$y}\" fill=\"#f8fafc\" font-size=\"28\" font-family=\"Segoe UI, Arial, sans-serif\" opacity=\"0.92\">{$safeLine}</text>";
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800" role="img" aria-labelledby="title desc">
  <title id="title">{$title}</title>
  <desc id="desc">{$description}</desc>
  <defs>
    <linearGradient id="bg" x1="0%" x2="100%" y1="0%" y2="100%">
      <stop offset="0%" stop-color="{$primary}" />
      <stop offset="52%" stop-color="{$secondary}" />
      <stop offset="100%" stop-color="{$accent}" />
    </linearGradient>
    <linearGradient id="glass" x1="0%" x2="100%" y1="0%" y2="0%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.22" />
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0.05" />
    </linearGradient>
  </defs>

  <rect width="1200" height="800" rx="36" fill="url(#bg)" />
  <circle cx="970" cy="154" r="160" fill="#ffffff" fill-opacity="0.08" />
  <circle cx="1090" cy="52" r="110" fill="#ffffff" fill-opacity="0.10" />
  <circle cx="1045" cy="560" r="220" fill="#ffffff" fill-opacity="0.06" />
  <path d="M0 680 C240 580 410 570 620 650 C810 720 1020 740 1200 640 L1200 800 L0 800 Z" fill="#0f172a" fill-opacity="0.18" />

  <g transform="translate(80 72)">
    <rect x="0" y="0" width="196" height="56" rx="18" fill="#ffffff" fill-opacity="0.16" />
    <text x="24" y="36" fill="#ffffff" font-size="24" font-weight="700" font-family="Segoe UI, Arial, sans-serif" letter-spacing="3">PRO REFORMAS VLC</text>
  </g>

  <g transform="translate(84 168)">
    <text x="0" y="0" fill="#f8fafc" font-size="76" font-weight="800" font-family="Segoe UI, Arial, sans-serif">{$title}</text>
    <rect x="0" y="56" width="520" height="4" rx="2" fill="#ffffff" fill-opacity="0.72" />
  </g>

  <g transform="translate(84 258)">
    <rect x="0" y="0" width="716" height="352" rx="30" fill="url(#glass)" stroke="#ffffff" stroke-opacity="0.18" />
    {$textBlocks}
  </g>

  <g transform="translate(878 184)">
    <rect x="0" y="0" width="220" height="220" rx="42" fill="#ffffff" fill-opacity="0.14" />
    <rect x="28" y="28" width="164" height="164" rx="34" fill="#0f172a" fill-opacity="0.16" />
    <text x="110" y="126" text-anchor="middle" fill="#ffffff" font-size="86" font-weight="800" font-family="Segoe UI, Arial, sans-serif">{$badge}</text>
  </g>

  <g transform="translate(790 520)">
    <rect x="0" y="0" width="324" height="118" rx="26" fill="#ffffff" fill-opacity="0.14" />
    <text x="28" y="48" fill="#ffffff" font-size="22" font-family="Segoe UI, Arial, sans-serif" opacity="0.82">Card visual</text>
    <text x="28" y="84" fill="#ffffff" font-size="36" font-weight="700" font-family="Segoe UI, Arial, sans-serif">1200 x 800 px</text>
  </g>
</svg>
SVG;
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $limit): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = trim($current.' '.$word);
            if ($current !== '' && mb_strlen($candidate) > $limit) {
                $lines[] = $current;
                $current = $word;
                continue;
            }

            $current = $candidate;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, 4);
    }
}
