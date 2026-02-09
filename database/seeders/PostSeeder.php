<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 유저 ID 가져오기 (
        $jungminId = DB::table('users')->where('email', 'jungmin@omamori.com')->value('id');
        $user1Id = DB::table('users')->where('email', 'user1@example.com')->value('id');
        $user2Id = DB::table('users')->where('email', 'user2@example.com')->value('id');
        
        // 유저가 없으면 에러 발생
        if (!$jungminId || !$user1Id || !$user2Id) {
            $this->command->error('Users not found! Run UserSeeder first.');
            return;
        }
        
        // Published 오마모리 찾기 
        $publishedOmamoris = DB::table('omamoris')
            ->where('status', 'published')
            ->get();
        
        // 정민의 published 오마모리
        $jungminOmamori = $publishedOmamoris->where('user_id', $jungminId)->first();
        
        // user1의 published 오마모리
        $user1Omamori = $publishedOmamoris->where('user_id', $user1Id)->first();

        $posts = [
            [
                'user_id' => $jungminId,
                'omamori_id' => $jungminOmamori?->id, // PHP 8.0+ null-safe operator
                'title' => '첫 오마모리 완성했습니다!',
                'content' => '드디어 첫 작품을 완성했어요. 자격증 시험을 앞두고 있어서 합격을 기원하는 의미로 만들어봤습니다. 여러분도 응원해주세요!',
                'like_count' => 12,
                'comment_count' => 3,
                'bookmark_count' => 5,
                'view_count' => 156,
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'user_id' => $user1Id,
                'omamori_id' => $user1Omamori?->id,
                'title' => '오마모리 제작 팁 공유합니다',
                'content' => '여러 개 만들어보니 나름의 노하우가 생겼어요. 색상 조합이 가장 중요한 것 같아요. 행운 컬러를 적극 활용하면 더 예쁜 작품이 나옵니다!',
                'like_count' => 28,
                'comment_count' => 7,
                'bookmark_count' => 15,
                'view_count' => 289,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => $user2Id,
                'omamori_id' => null,
                'title' => '프레임 추천 부탁드려요',
                'content' => '연애운 오마모리를 만들고 있는데, 어떤 프레임이 어울릴까요? 벚꽃 프레임과 금박 프레임 사이에서 고민 중입니다.',
                'like_count' => 5,
                'comment_count' => 12,
                'bookmark_count' => 2,
                'view_count' => 78,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        foreach ($posts as $post) {
            DB::table('posts')->insert($post);
        }
        
        $this->command->info('PostSeeder 성공적으로 실행됨');
    }
}