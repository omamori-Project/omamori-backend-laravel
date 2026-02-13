<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id()->comment('게시글 PK');

            $table->foreignId('user_id')
                ->comment('작성자 users.id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('omamori_id')
                ->comment('연결된 오마모리(필수) omamoris.id')
                ->constrained('omamoris')
                ->restrictOnDelete();

            $table->string('title', 150)->comment('게시글 제목');
            $table->text('content')->comment('게시글 본문');

            $table->jsonb('omamori_snapshot')->comment('오마모리 스냅샷(JSON, 게시 시점 고정)');
            $table->jsonb('tags')->nullable()->comment('태그 목록(JSON 배열, nullable)');

            $table->timestampTz('hidden_at')->nullable()->comment('숨김 처리 시각');

            $table->unsignedInteger('like_count')->default(0)->comment('좋아요 수(캐시)');
            $table->unsignedInteger('comment_count')->default(0)->comment('댓글 수(캐시)');
            $table->unsignedInteger('bookmark_count')->default(0)->comment('북마크 수(캐시)');
            $table->unsignedInteger('view_count')->default(0)->comment('조회수(캐시)');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['user_id', 'created_at'], 'posts_user_id_created_at_index');
            $table->index(['omamori_id', 'created_at'], 'posts_omamori_id_created_at_index');
            $table->index(['created_at'], 'posts_created_at_index');
            $table->index(['hidden_at'], 'posts_hidden_at_index');
        });

        DB::statement("comment on table posts is '커뮤니티 게시글'");
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};