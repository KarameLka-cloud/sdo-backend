<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        $this->migrateFrom('education_events', 'education', 'event', [
            'title', 'description', 'link', 'department_id', 'note_department', 'time', 'date', 'duration',
        ]);
        $this->migrateFrom('edo_events', 'edo', 'event', [
            'title', 'description', 'link', 'department_id', 'note_department', 'time', 'date', 'duration',
        ]);
        $this->migrateFrom('education_courses', 'education', 'course', [
            'title', 'description', 'link', 'department_id', 'note_department', 'date', 'duration',
        ]);
        $this->migrateFrom('edo_courses', 'edo', 'course', [
            'title', 'description', 'link', 'department_id', 'note_department', 'date', 'duration',
        ]);
        $this->migrateFrom('education_webinars', 'education', 'webinar', [
            'title', 'description', 'link', 'time', 'date', 'duration',
        ]);
        $this->migrateFrom('education_tests', 'education', 'test', [
            'title', 'description', 'link', 'position_id', 'note_position', 'date', 'duration',
        ]);
        $this->migrateFrom('edo_tests', 'edo', 'test', [
            'title', 'description', 'link', 'position_id', 'note_position', 'date', 'duration',
        ]);

        Schema::dropIfExists('education_events');
        Schema::dropIfExists('edo_events');
        Schema::dropIfExists('education_courses');
        Schema::dropIfExists('edo_courses');
        Schema::dropIfExists('education_webinars');
        Schema::dropIfExists('education_tests');
        Schema::dropIfExists('edo_tests');
    }

    public function down(): void
    {
        Schema::create('education_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->string('note_department')->nullable();
            $table->time('time')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('edo_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->string('note_department')->nullable();
            $table->time('time')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('education_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link');
            $table->foreignId('department_id')->constrained('departments');
            $table->string('note_department')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('edo_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link');
            $table->foreignId('department_id')->constrained('departments');
            $table->string('note_department')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('education_webinars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->time('time')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('education_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link');
            $table->foreignId('position_id')->constrained('positions');
            $table->string('note_position')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('edo_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link');
            $table->foreignId('position_id')->constrained('positions');
            $table->string('note_position')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();
        });

        $map = [
            ['education', 'event', 'education_events'],
            ['edo', 'event', 'edo_events'],
            ['education', 'course', 'education_courses'],
            ['edo', 'course', 'edo_courses'],
            ['education', 'webinar', 'education_webinars'],
            ['education', 'test', 'education_tests'],
            ['edo', 'test', 'edo_tests'],
        ];

        foreach ($map as [$category, $type, $table]) {
            if (!Schema::hasTable('learning_items') || !Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table('learning_items')
                ->where('category', $category)
                ->where('type', $type)
                ->get();

            foreach ($rows as $row) {
                $payload = [
                    'title' => $row->title,
                    'description' => $row->description,
                    'link' => $row->link,
                    'date' => $row->date,
                    'duration' => $row->duration,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];

                if (in_array($type, ['event', 'course'], true)) {
                    $payload['department_id'] = $row->department_id;
                    $payload['note_department'] = $row->note_department;
                }

                if (in_array($type, ['event', 'webinar'], true)) {
                    $payload['time'] = $row->time;
                }

                if ($type === 'test') {
                    $payload['position_id'] = $row->position_id;
                    $payload['note_position'] = $row->note_position;
                }

                DB::table($table)->insert($payload);
            }
        }

        Schema::dropIfExists('learning_items');
    }

    private function migrateFrom(string $table, string $category, string $type, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $rows = DB::table($table)->get();

        foreach ($rows as $row) {
            $payload = [
                'category' => $category,
                'type' => $type,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ];

            foreach ($columns as $column) {
                $payload[$column] = $row->{$column} ?? null;
            }

            DB::table('learning_items')->insert($payload);
        }
    }
};
