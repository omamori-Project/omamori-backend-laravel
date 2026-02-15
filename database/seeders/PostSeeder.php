<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 유저 ID 가져오기
        $jungminId = DB::table('users')->where('email', 'jungmin@omamori.com')->value('id');
        $user1Id   = DB::table('users')->where('email', 'user1@example.com')->value('id');
        $user2Id   = DB::table('users')->where('email', 'user2@example.com')->value('id');

        if (!$jungminId || !$user1Id || !$user2Id) {
            $this->command->error('Users not found! Run UserSeeder first.');
            return;
        }

        /**
         * 스냅샷 생성
         * - posts.omamori_snapshot (NOT NULL) 채우기
         */
        $makeSnapshot = function (?int $omamoriId): string {
            if (!$omamoriId) {
                return json_encode((object) [], JSON_UNESCAPED_UNICODE);
            }

            $omamori = DB::table('omamoris')->where('id', $omamoriId)->first();
            if (!$omamori) {
                return json_encode((object) [], JSON_UNESCAPED_UNICODE);
            }

            $fortuneColor = null;
            if (!empty($omamori->fortune_color_id)) {
                $fortuneColor = DB::table('fortune_colors')->where('id', $omamori->fortune_color_id)->first();
            }

            $frame = null;
            if (!empty($omamori->frame_id)) {
                $frame = DB::table('frames')->where('id', $omamori->frame_id)->first();
            }

            $elements = DB::table('omamori_elements')
                ->where('omamori_id', $omamoriId)
                ->orderBy('layer')
                ->get()
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'type' => $e->type,
                        'layer' => $e->layer,
                        'props' => is_string($e->props) ? json_decode($e->props, true) : $e->props,
                        'transform' => is_string($e->transform) ? json_decode($e->transform, true) : $e->transform,
                    ];
                })
                ->values()
                ->all();

            $snapshot = [
                'id' => $omamori->id,
                'title' => $omamori->title ?? null,
                'meaning' => $omamori->meaning ?? null,
                'status' => $omamori->status ?? null,
                'theme' => $omamori->theme ?? null,
                'size_code' => $omamori->size_code ?? null,
                'back_message' => $omamori->back_message ?? null,
                'published_at' => $omamori->published_at ?? null,
                'fortune_color' => $fortuneColor ? [
                    'id' => $fortuneColor->id,
                    'code' => $fortuneColor->code,
                    'name' => $fortuneColor->name,
                    'hex' => $fortuneColor->hex,
                    'short_meaning' => $fortuneColor->short_meaning,
                ] : null,
                'frame' => $frame ? [
                    'id' => $frame->id,
                    'name' => $frame->name,
                    'frame_key' => $frame->frame_key,
                    'preview_url' => $frame->preview_url,
                ] : null,
                'elements' => $elements,
            ];

            return json_encode($snapshot, JSON_UNESCAPED_UNICODE);
        };

        /**
         * 특정 유저의 published omamori_id 확보 (없으면 fallback)
         */
        $pickPublishedOmamoriId = function (int $userId): ?int {
            // 1) 해당 유저 published
            $id = DB::table('omamoris')
                ->where('user_id', $userId)
                ->where('status', 'published')
                ->value('id');

            if ($id) return $id;

            // 2) 아무 published
            $id = DB::table('omamoris')
                ->where('status', 'published')
                ->value('id');

            if ($id) return $id;

            // 3) published 자체가 없으면 -> 이 유저의 omamori 하나를 published로 강제
            $draftId = DB::table('omamoris')
                ->where('user_id', $userId)
                ->value('id');

            if (!$draftId) {
                return null;
            }

            DB::table('omamoris')
                ->where('id', $draftId)
                ->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'updated_at' => now(),
                ]);

            return $draftId;
        };

        $jungminOmamoriId = $pickPublishedOmamoriId($jungminId);
        $user1OmamoriId   = $pickPublishedOmamoriId($user1Id);
        $user2OmamoriId   = $pickPublishedOmamoriId($user2Id);

        $posts = [
            [
                'user_id' => $jungminId,
                'omamori_id' => $jungminOmamoriId,
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
                'omamori_id' => $user1OmamoriId,
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
                'omamori_id' => $user2OmamoriId,
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

        $posts = array_values(array_filter($posts, fn ($p) => !empty($p['omamori_id'])));

        foreach ($posts as $post) {
            $post['omamori_snapshot'] = $makeSnapshot($post['omamori_id']);
            DB::table('posts')->insert($post);
        }

        $this->command->info('PostSeeder 성공적으로 실행됨');
    }
}