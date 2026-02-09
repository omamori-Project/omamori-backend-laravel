<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('로그인 수단 PK');

            $table->unsignedBigInteger('user_id')
                ->comment('users.id 참조');

            $table->string('provider', 30)
                ->comment('로그인 제공자 (local | google)');

            $table->string('provider_user_id', 255)
                ->comment('제공자 내부 유저 식별자');

            $table->string('email', 255)
                ->nullable()
                ->comment('제공자 이메일');

            $table->string('password_hash', 255)
                ->nullable()
                ->comment('local 로그인 시 비밀번호 해시');

            $table->jsonb('profile')
                ->default(DB::raw("'{}'::jsonb"))
                ->comment('OAuth 프로필 최소 캐시');

            $table->timestampTz('linked_at')
                ->useCurrent()
                ->comment('계정 연결 시각');

            $table->timestampTz('last_used_at')
                ->nullable()
                ->comment('마지막 사용 시각');

            $table->timestampTz('revoked_at')
                ->nullable()
                ->comment('연결 해제 시각');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        DB::statement("comment on table user_identities is '유저 로그인 수단 (Local/OAuth) 테이블'");

    }

    public function down(): void
    {
        Schema::dropIfExists('user_identities');
    }
};