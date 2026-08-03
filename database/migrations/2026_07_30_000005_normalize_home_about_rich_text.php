<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'inicio')->first();
        if (! $page || ! $page->content || str_contains($page->content, '<p')) {
            return;
        }

        $paragraphs = preg_split('/<br\s*\/?>/i', $page->content);
        $content = collect($paragraphs)
            ->map(fn ($paragraph) => trim($paragraph))
            ->filter()
            ->map(fn ($paragraph) => "<p>{$paragraph}</p>")
            ->implode('');

        DB::table('pages')->where('id', $page->id)->update([
            'content' => $content,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        //
    }
};
