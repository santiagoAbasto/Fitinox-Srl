<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateIntegrationToken extends Command
{
    protected $signature = 'integration:token
        {--write : Guarda el token en el archivo .env}
        {--force : Reemplaza un token existente al usar --write}';

    protected $description = 'Genera un token seguro para la integración privada con el sistema de gestión.';

    public function handle(): int
    {
        $token = bin2hex(random_bytes(48));

        if (! $this->option('write')) {
            $this->line($token);
            $this->newLine();
            $this->warn('El token fue generado pero no se guardó. Copialo de forma segura o ejecutá: php artisan integration:token --write');

            return self::SUCCESS;
        }

        $path = base_path('.env');
        if (! File::exists($path)) {
            $this->error('No se encontró el archivo .env.');

            return self::FAILURE;
        }

        $contents = File::get($path);
        $pattern = '/^FITINOX_INTEGRATION_TOKEN=(.*)$/m';
        $existing = preg_match($pattern, $contents, $matches) ? trim($matches[1], " \	\\\"'") : '';
        if ($existing !== '' && ! $this->option('force')) {
            $this->error('Ya existe un token. Para reemplazarlo explícitamente usá: php artisan integration:token --write --force');

            return self::FAILURE;
        }

        $line = 'FITINOX_INTEGRATION_TOKEN='.$token;
        $updated = preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents, 1)
            : rtrim($contents).PHP_EOL.PHP_EOL.$line.PHP_EOL;
        File::put($path, $updated, true);

        $this->info('Token de integración guardado en .env.');
        $this->line($token);
        $this->warn('Guardalo en un gestor de contraseñas y compartilo sólo con quien configure el conector.');

        return self::SUCCESS;
    }
}
