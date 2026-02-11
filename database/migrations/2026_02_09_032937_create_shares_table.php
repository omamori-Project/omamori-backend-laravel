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
            $table->unsignedBigInteger('user_id')->comment('공유 생성자');

            $table->uuid('token')->unique()->comment('공유 토큰(UUID)');
            $table->boolean('is_active')->default(true)->comment('공유 활성 여부');
            $table->integer('view_count')->default(0)->comment('공유 조회수');

            $table->timestampTz('expires_at')->nullable()->comment('만료 시각');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('omamori_id')->references('id')->on('omamoris')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index('omamori_id', 'idx_shares_omamori');
            $table->index('token', 'idx_shares_token');
            $table->index('expires_at', 'idx_shares_expires_at');
        });

        DB::statement("comment on table shares is '오마모리 공유 링크'");
    }

    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};