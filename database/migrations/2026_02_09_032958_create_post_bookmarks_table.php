<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_bookmarks', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->comment('대상 게시글 posts.id');
            $table->unsignedBigInteger('user_id')->comment('북마크한 유저 users.id');
        
            $table->timestampTz('created_at')->useCurrent()->comment('북마크 시각');
        
            $table->primary(['post_id', 'user_id']);
        
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        DB::statement("comment on table post_bookmarks is '게시글 북마크(유저-게시글 매핑)'");        
    }

    public function down(): void
    {
        Schema::dropIfExists('post_bookmarks');
    }
};