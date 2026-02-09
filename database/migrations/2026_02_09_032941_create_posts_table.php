<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('게시글 PK');
        
            $table->unsignedBigInteger('user_id')->comment('작성자 users.id');
            $table->unsignedBigInteger('omamori_id')->nullable()->comment('연결된 오마모리(선택)');
        
            $table->string('title', 150)->comment('게시글 제목');
            $table->text('content')->comment('게시글 본문');
        
            $table->integer('like_count')->default(0)->comment('좋아요 수(캐시)');
            $table->integer('comment_count')->default(0)->comment('댓글 수(캐시)');
            $table->integer('bookmark_count')->default(0)->comment('북마크 수(캐시)');
            $table->integer('view_count')->default(0)->comment('조회수(캐시)');
        
            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('updated_at')->useCurrent()->comment('수정 시각');
            $table->timestampTz('deleted_at')->nullable()->comment('소프트 삭제 시각');
        
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('omamori_id')->references('id')->on('omamoris');
        });
        
        DB::statement("comment on table posts is '커뮤니티 게시글'");
        
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};