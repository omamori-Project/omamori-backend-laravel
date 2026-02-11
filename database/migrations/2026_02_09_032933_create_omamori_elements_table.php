<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omamori_elements', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('요소 PK');
        
            $table->unsignedBigInteger('omamori_id')->comment('소속 오마모리');
        
            $table->string('type', 20)
                ->comment('요소 타입 (text | stamp | image | background)');
        
            $table->integer('layer')->default(0)->comment('레이어 순서');
        
            $table->jsonb('props')->comment('요소 속성');
            $table->jsonb('transform')->nullable()->comment('위치/회전/스케일 정보');
        
            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('updated_at')->useCurrent()->comment('수정 시각');
            $table->timestampTz('deleted_at')->nullable()->comment('소프트 삭제');
        
            $table->foreign('omamori_id')->references('id')->on('omamoris')->cascadeOnDelete();
        });
        
        DB::statement("comment on table omamori_elements is '오마모리 구성 요소'");
        
    }

    public function down(): void
    {
        Schema::dropIfExists('omamori_elements');
    }
};