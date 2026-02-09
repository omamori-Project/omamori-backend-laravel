<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,           // 1. 유저
            FortuneColorSeeder::class,   // 2. 마스터 데이터
            FrameSeeder::class,          // 3. 마스터 데이터
            OmamoriSeeder::class,        // 4. 오마모리 (user_id 필요)
            PostSeeder::class,           // 5. 게시글 (user_id, omamori_id)
            CommentSeeder::class,        // 6. 댓글 (post_id, user_id)
        ]);
    }
}