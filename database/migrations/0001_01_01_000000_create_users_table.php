<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('유저 PK');

            $table->string('email', 255)
                ->unique()
                ->comment('로그인 이메일 (OAuth 포함)');

            $table->string('name', 100)
                ->comment('표시 이름');

            $table->string('role', 20)
                ->default('user')
                ->comment('권한 (user | admin)');

            $table->boolean('is_active')
                ->default(true)
                ->comment('계정 활성 여부');

            $table->timestampTz('email_verified_at')
                ->nullable()
                ->comment('이메일 인증 시각');

            $table->timestampTz('last_login_at')
                ->nullable()
                ->comment('마지막 로그인 시각');

            $table->timestampTz('created_at')
                ->useCurrent()
                ->comment('생성 시각');

            $table->timestampTz('updated_at')
                ->useCurrent()
                ->comment('수정 시각');

            $table->timestampTz('deleted_at')
                ->nullable()
                ->comment('소프트 삭제 시각');
        });

        DB::statement("comment on table users is '유저 기본 계정 테이블'");

    }
    
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};