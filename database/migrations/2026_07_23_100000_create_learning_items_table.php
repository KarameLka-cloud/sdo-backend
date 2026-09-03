<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unifies the seven per-category learning tables into `learning_items`.
 *
 * The legacy tables were created by migrations that have since been removed,
 * so the copy step below only runs on databases that still carry them.
 */
return new class extends Migration
{
    /** [legacy table, category, type, copied columns] */
    private const LEGACY_SOURCES = [
        ['education_events', 'education', 'event', ['title', 'description', 'link', 'department_id', 'note_department', 'time', 'date', 'duration']],
        ['edo_events', 'edo', 'event', ['title', 'description', 'link', 'department_id', 'note_department', 'time', 'date', 'duration']],
        ['education_courses', 'education', 'course', ['title', 'description', 'link', 'department_id', 'note_department', 'date', 'duration']],
        ['edo_courses', 'edo', 'course', ['title', 'description', 'link', 'department_id', 'note_department', 'date', 'duration']],
        ['education_webinars', 'education', 'webinar', ['title', 'description', 'link', 'time', 'date', 'duration']],
        ['education_tests', 'education', 'test', ['title', 'description', 'link', 'position_id', 'note_position', 'date', 'duration']],
        ['edo_tests', 'edo', 'test', ['title', 'description', 'link', 'position_id', 'note_position', 'date', 'duration']],
    ];

    public function up(): void
    {
        Schema::create('learning_items', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // education | edo
            $table->string('type'); // event | course | webinar | test
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('note_department')->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('note_position')->nullable();
            $table->time('time')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();

            $table->index(['category', 'type', 'date']);
        });

        foreach (self::LEGACY_SOURCES as [$table, $category, $type, $columns]) {
            $this->copyLegacyTable($table, $category, $type, $columns);
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_items');
    }

    private function copyLegacyTable(string $table, string $category, string $type, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)->orderBy('id')->chunkById(500, function ($rows) use ($category, $type, $columns) {
            $payload = $rows->map(fn ($row) => [
                'category' => $category,
                'type' => $type,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
                ...array_combine(
                    $columns,
                    array_map(fn ($column) => $row->{$column} ?? null, $columns),
                ),
            ])->all();

            DB::table('learning_items')->insert($payload);
        });
    }
};
