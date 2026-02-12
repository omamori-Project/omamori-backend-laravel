<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('파일 PK');

            $table->unsignedBigInteger('user_id')
                ->comment('파일 소유 유저');

            // 어떤 오마모리에서 생성/소속된 파일인지 추적 (nullable)
            $table->unsignedBigInteger('omamori_id')
                ->nullable()
                ->comment('연관 오마모리 (nullable)');

            $table->string('purpose', 30)
                ->comment('파일 용도 (omamori_element | render_output | frame_asset | profile_image)');

            $table->string('visibility', 10)
                ->default('public')
                ->comment('공개 범위 (public | private)');

            $table->text('file_key')
                ->unique()
                ->comment('스토리지 내부 키');

            $table->text('url')
                ->comment('접근 URL');

            $table->string('content_type', 100)
                ->nullable()
                ->comment('MIME 타입');

            $table->unsignedBigInteger('size_bytes')
                ->nullable()
                ->comment('파일 크기 (bytes)');

            $table->integer('width')
                ->nullable()
                ->comment('이미지 너비');

            $table->integer('height')
                ->nullable()
                ->comment('이미지 높이');

            $table->timestampTz('created_at')
                ->useCurrent()
                ->comment('업로드 시각');

            $table->timestampTz('deleted_at')
                ->nullable()
                ->comment('소프트 삭제 시각');

            // FK
            $table->foreign('user_id')->references('id')->on('users');

            // index (조회 최적화)
            $table->index('omamori_id');
            $table->index(['user_id', 'purpose']);
        });

        DB::statement("comment on table files is '업로드/생성 파일 메타데이터'");
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};