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
                ->comment('프레임 식별 키');

            $table->text('preview_url')
                ->nullable()
                ->comment('미리보기 이미지 URL');

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
            $table->timestampTz('deleted_at')->nullable()->comment('소프트 삭제 시각');

            $table->foreign('asset_file_id')->references('id')->on('files');
        });

        DB::statement("comment on table frames is '오마모리 프레임'");

        DB::statement("create unique index frames_frame_key_unique on frames(frame_key) where deleted_at is null");
        DB::statement("create index idx_frames_deleted_at on frames(deleted_at)");
        DB::statement("create index idx_frames_asset_file_id on frames(asset_file_id)");
    }

    public function down(): void
    {
        Schema::dropIfExists('frames');
    }
};