<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    #[DataProvider('xssPayloads')]
    public function test_it_removes_active_content(string $payload): void
    {
        $clean = app(HtmlSanitizer::class)->sanitize($payload);
        $this->assertDoesNotMatchRegularExpression('/<script|\son\w+\s*=|javascript:|<svg/i', $clean);
    }

    public static function xssPayloads(): array
    {
        return [
            ['<script>alert(1)</script>'],
            ['<img src=x onerror=alert(1)>'],
            ['<svg onload=alert(1)>'],
            ['<a href="javascript:alert(1)" onclick="alert(2)">Abrir</a>'],
        ];
    }

    public function test_it_preserves_allowed_rich_text_and_safe_links(): void
    {
        $clean = app(HtmlSanitizer::class)->sanitize('<p><strong>Texto</strong> <a href="https://example.com">Enlace</a></p>');
        $this->assertStringContainsString('<strong>Texto</strong>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }
}
