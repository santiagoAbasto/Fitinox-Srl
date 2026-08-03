<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pages')->updateOrInsert(['slug' => 'nosotros'], [
            'title' => 'Nosotros',
            'template' => 'about',
            'hero_title' => 'Nosotros',
            'hero_image' => 'figma/nosotros/hero.png',
            'content' => '<p>Nuestra Empresa es una organización pyme que se desarrolla en dos aspectos, uno industrial dedicado a la fabricación de conexiones, válvulas aguja y válvulas múltiples y otro comercial dedicado a la venta de materiales para la conducción de fluidos con especialidad en productos de aceros inoxidables, aceros al carbono y aleaciones especiales.</p><p>Somos una industria metalmecánica productora de conectores a compresión y accesorios roscados y para soldar en aceros inoxidables, carbono y bronce, especializada en el mecanizado de precisión tanto partiendo de barras trefiladas como de piezas forjadas.</p><p>Durante la década del 90 y como consecuencia de la importación indiscriminada a precios muy inferiores a los costos de materias primas en muchos casos, tomamos la decisión de fortalecer nuestro sector comercial y especialmente el suministro a obras de grandes empresas que valoran además del costo absoluto del producto, el servicio prestado, lo que nos permitió participar como proveedores en importantes construcciones.</p><p>Esta relación de servicio facilitó además la provisión como fabricantes de todos aquellos materiales que por distintas razones no se importaban y que nos permitió mantener el ritmo productivo sin mayores interferencias aún en los momentos más difíciles.</p>',
            'sections' => json_encode([
                'content_title' => '¿Quienes somos?',
                'content_image' => 'figma/nosotros/about.png',
                'values_title' => '¿Por qué elegirnos?',
                'mission_title' => 'Misión',
                'mission_text' => 'Contar con la mas amplia gama de accesorios, bridas, valvulas y tuberias en acero inoxidable para cualquier tipo de fluidos y para todas las industrias en general.',
                'mission_icon' => 'figma/nosotros/mission.svg',
                'vision_title' => 'Visión',
                'vision_text' => 'Nuestros grandes objetivos a mediano y largo plazo son; lograr la informatización y digitalización de nuestra empresa abandonando definitivamente los papeles en planta y administración, cruzar los limites de nuestras fronteras hacia todo sudamerica.',
                'vision_icon' => 'figma/nosotros/vision.svg',
                'quality_title' => 'Calidad',
                'quality_text' => 'Nos impulsa la innovación, la calidad y la precisión, con un compromiso constante hacia la mejora continua y la confianza de quienes nos eligen.',
                'quality_icon' => 'figma/nosotros/quality.svg',
                'video_cover' => 'figma/nosotros/video-cover.png',
                'video_url' => '',
            ]),
            'seo_title' => 'Nosotros | Fitinox',
            'seo_description' => 'Conocé la historia, misión, visión y compromiso con la calidad de Fitinox.',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->where('slug', 'nosotros')->delete();
    }
};
