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
                ->nullable()
                ->comment('연결된 오마모리(선택)')
                ->constrained('omamoris')
                ->nullOnDelete();

            $table->string('title', 150)->comment('게시글 제목');
            $table->text('content')->comment('게시글 본문');

            $table->unsignedInteger('like_count')->default(0)->comment('좋아요 수(캐시)');
            $table->unsignedInteger('comment_count')->default(0)->comment('댓글 수(캐시)');
            $table->unsignedInteger('bookmark_count')->default(0)->comment('북마크 수(캐시)');
            $table->unsignedInteger('view_count')->default(0)->comment('조회수(캐시)');

            $table->timestampsTz();
            $table->softDeletesTz();

            // Feed / 내 글 / 유저 글 목록 성능용 인덱스
            $table->index(['user_id', 'created_at'], 'posts_user_id_created_at_index');
            $table->index(['omamori_id', 'created_at'], 'posts_omamori_id_created_at_index');
            $table->index(['created_at'], 'posts_created_at_index');
        });

        DB::statement("comment on table posts is '커뮤니티 게시글'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};