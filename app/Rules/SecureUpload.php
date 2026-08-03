<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureUpload implements ValidationRule
{
    private const FORBIDDEN = ['php', 'phtml', 'pht', 'phar', 'cgi', 'pl', 'py', 'sh', 'exe', 'bat', 'cmd', 'com', 'js', 'html', 'htm', 'shtml', 'svg'];

    public function __construct(private readonly bool $image = false, private readonly int $maxPixels = 40_000_000) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('El archivo no se pudo validar.');
            return;
        }

        $name = strtolower($value->getClientOriginalName());
        $segments = array_values(array_filter(explode('.', $name), fn ($part) => $part !== ''));
        if (count($segments) < 2 || collect($segments)->contains(fn ($part) => in_array($part, self::FORBIDDEN, true))) {
            $fail('El nombre o la extensión del archivo no están permitidos.');
            return;
        }

        if ($this->image) {
            $dimensions = @getimagesize($value->getRealPath());
            if (! $dimensions || ($dimensions[0] * $dimensions[1]) > $this->maxPixels) {
                $fail('La imagen es inválida o supera las dimensiones máximas permitidas.');
            }
        }
    }
}
