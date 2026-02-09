<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('공유 PK');
        
            $table->unsignedBigInteger('omamori_id')->comment('공유 대상 오마모리');
        
            $table->string('share_code', 32)->unique()->comment('공유 코드');
            $table->boolean('is_public')->default(true)->comment('공개 여부');
            $table->integer('view_count')->default(0)->comment('공유 조회수');
        
            $table->timestampTz('expires_at')->nullable()->comment('만료 시각');
            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('revoked_at')->nullable()->comment('회수 시각');
        
            $table->foreign('omamori_id')->references('id')->on('omamoris')->cascadeOnDelete();
        });
        
        DB::statement("comment on table shares is '오마모리 공유 링크'");
        
    }

    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};