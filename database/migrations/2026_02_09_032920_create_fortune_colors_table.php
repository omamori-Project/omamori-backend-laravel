<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fortune_colors', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('포춘 컬러 PK');

            $table->string('code', 60)
                ->unique()
                ->comment('고유 코드 (seed 기준)');

            $table->string('name', 60)
                ->comment('컬러 이름');

            $table->string('hex', 7)
                ->comment('HEX 색상값 (#RRGGBB)');

            $table->string('category', 30)
                ->nullable()
                ->comment('카테고리');

            $table->string('short_meaning', 120)
                ->nullable()
                ->comment('짧은 의미 설명');

            $table->text('meaning')
                ->nullable()
                ->comment('상세 의미');

            $table->jsonb('tips')
                ->default(DB::raw("'[]'::jsonb"))
                ->comment('행동 팁 리스트');

            $table->boolean('is_active')
                ->default(true)
                ->comment('활성 여부');

            $table->timestampTz('created_at')
                ->useCurrent()
                ->comment('생성 시각');

            $table->timestampTz('updated_at')
                ->useCurrent()
                ->comment('수정 시각');
        });

        DB::statement("comment on table fortune_colors is '포춘 컬러 테이블'");

    }
    
    public function down(): void
    {
        Schema::dropIfExists('fortune_colors');
    }
};