<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renders', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('렌더 작업 PK');
        
            $table->string('render_code', 40)->unique()->comment('렌더 식별 코드');
            $table->unsignedBigInteger('user_id')->comment('요청 유저 users.id');
            $table->unsignedBigInteger('omamori_id')->nullable()->comment('대상 오마모리(선택)');
        
            $table->string('side', 10)->default('front')->comment('렌더 범위 (front | back | both)');
            $table->string('format', 10)->default('png')->comment('출력 포맷 (png | jpg | pdf)');
        
            $table->integer('dpi')->default(150)->comment('출력 DPI');
            $table->integer('width')->nullable()->comment('출력 너비(px)');
            $table->integer('height')->nullable()->comment('출력 높이(px)');
        
            $table->string('store', 10)->default('temp')->comment('저장 정책 (temp | persist)');
        
            $table->unsignedBigInteger('file_id')->nullable()->comment('생성된 파일 files.id');
            $table->timestampTz('expires_at')->nullable()->comment('만료 시각(temp 정리용)');
            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
        
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('omamori_id')->references('id')->on('omamoris');
            $table->foreign('file_id')->references('id')->on('files');
        });
        
        DB::statement("comment on table renders is '오마모리 렌더링(이미지/PDF 생성 작업)'");        
    }

    public function down(): void
    {
        Schema::dropIfExists('renders');
    }
};