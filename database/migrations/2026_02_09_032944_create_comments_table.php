<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - comments: 게시글 댓글/답글
     * - parent_id: null이면 댓글, 값이 있으면 답글
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->id()->comment('댓글 PK');

            $table->foreignId('post_id')
                ->comment('대상 게시글 posts.id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->comment('작성자 users.id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->comment('부모 댓글(답글용), comments.id')
                ->constrained('comments')
                ->nullOnDelete();

            $table->text('content')->comment('댓글 내용');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['post_id', 'created_at'], 'comments_post_id_created_at_index');
            $table->index(['parent_id', 'created_at'], 'comments_parent_id_created_at_index');
            $table->index(['user_id', 'created_at'], 'comments_user_id_created_at_index');
        });

        DB::statement("comment on table comments is '게시글 댓글/답글'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};