<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file')->nullable();
            $table->string('logo')->nullable();
            $table->string('sort_order', 3)->default('A');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('pages')->updateOrInsert(
            ['slug' => 'calidad'],
            [
                'title' => 'Calidad',
                'template' => 'quality',
                'hero_title' => 'Calidad',
                'hero_image' => 'figma/quality/hero.png',
                'content' => 'En Fitinox S.R.L. trabajamos bajo un compromiso permanente con la excelencia. Nuestro Sistema de Gestión de la Calidad se fundamenta en principios que aseguran la solidez de nuestros procesos, el desarrollo de nuestra gente y la mejora continua de cada proyecto que realizamos. Estas políticas guían nuestras acciones diarias y reflejan nuestra responsabilidad con nuestros clientes, colaboradores y la comunidad.',
                'sections' => json_encode([
                    'content_title' => 'Políticas de calidad',
                    'content_image' => 'figma/quality/content.png',
                    'downloads_title' => 'Descargas',
                    'policies' => [
                        'Defensa del buen nombre de la empresa.',
                        'Aseguramiento total de la calidad independientemente de los condicionamientos del mercado.',
                        'Búsqueda permanente de la innovación tecnológica.',
                        'Actualización continua de sistemas y procesos para mejorar el Sistema de Gestión de la Calidad.',
                        'Capacitación y desarrollo de los Recursos Humanos de la Empresa.',
                        'Cumplimiento de requisitos aplicables.',
                    ],
                ]),
                'seo_title' => 'Calidad | Fitinox',
                'seo_description' => 'Políticas, certificaciones y documentación del Sistema de Gestión de la Calidad de Fitinox.',
                'is_published' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([
            ['Política de calidad', 'figma/quality/fitinox-logo.png', 'A'],
            ['ISO 9001:2015 Otorgado por DNV', 'figma/quality/dnv-logo.png', 'AA'],
            ['Norma ASTM F 1387 Otorgada por el Bureau Veritas', 'figma/quality/bureau-veritas-logo.png', 'AB'],
        ] as [$title, $logo, $order]) {
            DB::table('quality_documents')->insert([
                'title' => $title,
                'logo' => $logo,
                'sort_order' => $order,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_documents');
        DB::table('pages')->where('slug', 'calidad')->delete();
    }
};
