<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omamoris', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('오마모리 PK');

            $table->unsignedBigInteger('user_id')->comment('제작 유저');

            $table->string('title', 120)->comment('오마모리 제목');
            $table->text('meaning')->nullable()->comment('의미 설명');

            $table->string('status', 20)
                ->default('draft')
                ->comment('상태 (draft | published)');

            $table->string('theme', 30)->nullable()->comment('테마');
            $table->string('size_code', 10)->nullable()->comment('사이즈 코드');
            $table->text('back_message')->nullable()->comment('뒷면 메시지');

            $table->unsignedBigInteger('applied_fortune_color_id')->nullable()->comment('적용된 포춘 컬러');
            $table->unsignedBigInteger('applied_frame_id')->nullable()->comment('적용된 프레임');
            $table->unsignedBigInteger('preview_file_id')->nullable()->comment('미리보기 이미지');

            $table->integer('view_count')->default(0)->comment('조회수');
            $table->timestampTz('published_at')->nullable()->comment('게시 시각');

            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('updated_at')->useCurrent()->comment('수정 시각');
            $table->timestampTz('deleted_at')->nullable()->comment('소프트 삭제');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('applied_fortune_color_id')->references('id')->on('fortune_colors');
            $table->foreign('applied_frame_id')->references('id')->on('frames');
            $table->foreign('preview_file_id')->references('id')->on('files');
        });

        DB::statement("comment on table omamoris is '유저가 생성한 오마모리'");

    }

    public function down(): void
    {
        Schema::dropIfExists('omamoris');
    }
};