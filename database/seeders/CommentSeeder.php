<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $jungminId = DB::table('users')->where('email', 'jungmin@omamori.com')->value('id');
        $user1Id = DB::table('users')->where('email', 'user1@example.com')->value('id');
        $user2Id = DB::table('users')->where('email', 'user2@example.com')->value('id');
        
        // 게시글 가져오기
        $posts = DB::table('posts')->get();
        
        if ($posts->isEmpty()) {
            $this->command->warn('게시글이 없습니다. CommentSeeder를 건너뜁니다.');
            return;
        }
        
        $post1 = $posts->where('user_id', $jungminId)->first();
        $post2 = $posts->where('user_id', $user1Id)->first();
        $post3 = $posts->where('user_id', $user2Id)->first();
        // 게시글이 없으면 스킵
        if (!$post1 || !$post2 || !$post3) {
            $this->command->warn('댓글을 위한 게시글이 충분하지 않습니다. CommentSeeder를 건너뜁니다.');
            return;
        }

        $comments = [
            // 첫 번째 게시글 댓글
            [
                'post_id' => $post1->id,
                'user_id' => $user1Id,
                'parent_id' => null,
                'content' => '정말 예쁘게 만드셨네요! 시험 꼭 합격하세요!',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'post_id' => $post1->id,
                'user_id' => $user2Id,
                'parent_id' => null,
                'content' => '색상 조합이 정말 좋아요. 저도 도전해볼게요!',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            
            // 두 번째 게시글 댓글
            [
                'post_id' => $post2->id,
                'user_id' => $jungminId,
                'parent_id' => null,
                'content' => '유용한 정보 감사합니다! 참고할게요.',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'post_id' => $post3->id,
                'user_id' => $jungminId,
                'parent_id' => null,
                'content' => '프레임 선택에 도움이 되길 바랍니다!',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        foreach ($comments as $comment) {
            $commentId = DB::table('comments')->insertGetId($comment);
            
            // 첫 댓글에 답글 추가
            if ($comment['post_id'] === $post1->id && $comment['user_id'] === $user1Id) {
                DB::table('comments')->insert([
                    'post_id' => $post1->id,
                    'user_id' => $jungminId,
                    'parent_id' => $commentId,
                    'content' => '감사합니다! 덕분에 힘이 나네요 ㅎㅎ',
                    'created_at' => now()->subDays(6),
                    'updated_at' => now()->subDays(6),
                ]);
            }
        }
        
        $this->command->info('CommentSeeder 성공적으로 실행됨');
    }
}