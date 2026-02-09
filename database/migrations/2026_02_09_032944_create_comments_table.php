<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('댓글 PK');
        
            $table->unsignedBigInteger('post_id')->comment('대상 게시글 posts.id');
            $table->unsignedBigInteger('user_id')->comment('작성자 users.id');
        
            $table->unsignedBigInteger('parent_id')->nullable()->comment('부모 댓글(답글용), comments.id');
        
            $table->text('content')->comment('댓글 내용');
        
            $table->timestampTz('created_at')->useCurrent()->comment('생성 시각');
            $table->timestampTz('updated_at')->useCurrent()->comment('수정 시각');
            $table->timestampTz('deleted_at')->nullable()->comment('소프트 삭제 시각');
        
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('parent_id')->references('id')->on('comments');
        });
        
        DB::statement("comment on table comments is '게시글 댓글/답글'");        
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};