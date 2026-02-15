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

            $table->timestampTz('deleted_at')
                ->nullable()
                ->comment('소프트 삭제 시각');
        });

        DB::statement("comment on table fortune_colors is '포춘 컬러 테이블'");

        DB::statement("create unique index fortune_colors_code_unique on fortune_colors(code) where deleted_at is null");
        DB::statement("create index idx_fortune_colors_deleted_at on fortune_colors(deleted_at)");

        /**
         * users 테이블에 웹 테마 적용용 컬럼 추가 
         */
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'applied_fortune_color_id')) {
                $table->unsignedBigInteger('applied_fortune_color_id')
                    ->nullable()
                    ->after('last_login_at')
                    ->comment('웹 테마 적용 행운 컬러');

                $table->foreign('applied_fortune_color_id')
                    ->references('id')
                    ->on('fortune_colors')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // FK/컬럼 먼저 제거 후 fortune_colors drop
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'applied_fortune_color_id')) {
                $table->dropForeign(['applied_fortune_color_id']);
                $table->dropColumn('applied_fortune_color_id');
            }
        });

        Schema::dropIfExists('fortune_colors');
    }
};
