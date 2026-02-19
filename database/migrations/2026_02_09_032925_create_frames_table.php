<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('프레임 PK');

            $table->string('name', 80)
                ->comment('프레임 이름');

            $table->string('frame_key', 60)
                ->unique()
                ->comment('프레임 식별 키');

            $table->string('preview_path', 255)
                ->nullable()
                ->comment('미리보기 이미지 경로(스토리지 내부 경로)');

            $table->boolean('is_default')
                ->default(false)
                ->comment('기본 프레임 여부');

            $table->unsignedBigInteger('asset_file_id')
                ->nullable()
                ->comment('프레임 원본 파일');

            $table->boolean('is_active')
                ->default(true)
                ->comment('활성 여부');

            $table->jsonb('meta')
                ->default(DB::raw("'{}'::jsonb"))
                ->comment('프레임 메타 정보');

            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('updated_at')->useCurrent()->comment('수정 시각');
            $table->timestampTz('deleted_at')->nullable()->comment('삭제 시각(soft delete)');

            $table->index('deleted_at');

            $table->foreign('asset_file_id')->references('id')->on('files');
        });

        DB::statement("create unique index if not exists frames_unique_default on frames(is_default) where is_default = true");

        DB::statement("comment on table frames is '오마모리 프레임'");
    }

    public function down(): void
    {
        DB::statement("drop index if exists frames_unique_default");
        Schema::dropIfExists('frames');
    }
};