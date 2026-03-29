<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('question_tags')) {
            return;
        }

        if (! Schema::hasTable('tags') || ! Schema::hasTable('question_tag_map')) {
            return;
        }

        $legacyRows = DB::table('question_tags')
            ->select('question_id', 'tag')
            ->orderBy('question_id')
            ->get();

        foreach ($legacyRows as $legacyRow) {
            $tagName = trim((string) $legacyRow->tag);
            if ($tagName === '') {
                continue;
            }

            $tagId = DB::table('tags')->where('name', $tagName)->value('id');
            if (! $tagId) {
                $tagId = DB::table('tags')->insertGetId([
                    'name' => $tagName,
                    'slug' => $this->uniqueSlugForTag($tagName),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('question_tag_map')->insertOrIgnore([
                'question_id' => $legacyRow->question_id,
                'tag_id' => $tagId,
            ]);
        }

        Schema::dropIfExists('question_tags');
    }

    public function down(): void
    {
        if (! Schema::hasTable('question_tags')) {
            Schema::create('question_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
                $table->string('tag', 100);
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['question_id', 'tag']);
            });
        }

        if (! Schema::hasTable('question_tag_map') || ! Schema::hasTable('tags')) {
            return;
        }

        $normalizedRows = DB::table('question_tag_map')
            ->join('tags', 'tags.id', '=', 'question_tag_map.tag_id')
            ->select('question_tag_map.question_id', 'tags.name as tag')
            ->get();

        foreach ($normalizedRows as $normalizedRow) {
            DB::table('question_tags')->insertOrIgnore([
                'question_id' => $normalizedRow->question_id,
                'tag' => $normalizedRow->tag,
                'created_at' => now(),
            ]);
        }
    }

    private function uniqueSlugForTag(string $tagName): string
    {
        $baseSlug = Str::slug($tagName);
        if ($baseSlug === '') {
            $baseSlug = 'tag';
        }

        $slug = $baseSlug;
        $index = 1;

        while (DB::table('tags')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
    }
};
