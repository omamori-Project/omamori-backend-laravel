<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OmamoriSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1) 사용자 ID
         */
        $jungminId = DB::table('users')->where('email', 'jungmin@omamori.com')->value('id');
        $user1Id   = DB::table('users')->where('email', 'user1@example.com')->value('id');

        if (!$jungminId || !$user1Id) {
            throw new RuntimeException('Users not found. Run UserSeeder first.');
        }

        /**
         * 2) 행운 컬러 ID
         * - 없을 수 있으니 안전하게 처리
         */
        $redColorId  = DB::table('fortune_colors')->where('code', 'red_passion')->value('id');
        $blueColorId = DB::table('fortune_colors')->where('code', 'blue_peace')->value('id');
        $pinkColorId = DB::table('fortune_colors')->where('code', 'pink_love')->value('id'); // 없을 수도 있음

        /**
         * 3) 기본 프레임 ID (필수)
         */
        $defaultFrameId = DB::table('frames')->where('frame_key', 'traditional_wood')->value('id');

        if (!$defaultFrameId) {
            throw new RuntimeException('Default frame not found. Run FrameSeeder first (traditional_wood required).');
        }

        /**
         * 4) 선택 프레임 ID (없으면 기본 프레임으로 fallback)
         */
        $sakuraFrameId = DB::table('frames')->where('frame_key', 'sakura')->value('id');
        $user1FrameId = $sakuraFrameId ?: $defaultFrameId;

        /**
         * 5) 오마모리 데이터
         */
        $now = now();

        $omamoris = [
            [
                'user_id' => $jungminId,
                'title' => '합격 기원 오마모리',
                'meaning' => '2026년 자격증 시험 합격을 기원합니다',
                'status' => 'published',
                'theme' => 'success',
                'size_code' => 'standard',
                'back_message' => '열심히 공부한 만큼 좋은 결과가 있을 거예요!',
                'applied_fortune_color_id' => $redColorId,
                'applied_frame_id' => $defaultFrameId,
                'preview_file_id' => null,
                'view_count' => 150,
                'published_at' => $now->copy()->subDays(10),
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ],
            [
                'user_id' => $jungminId,
                'title' => '건강 기원 오마모리',
                'meaning' => '가족 모두의 건강을 기원합니다',
                'status' => 'published',
                'theme' => 'health',
                'size_code' => 'standard',
                'back_message' => '늘 건강하세요',
                'applied_fortune_color_id' => $blueColorId,
                'applied_frame_id' => $defaultFrameId,
                'preview_file_id' => null,
                'view_count' => 89,
                'published_at' => $now->copy()->subDays(5),
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ],
            [
                'user_id' => $jungminId,
                'title' => '제작 중인 오마모리',
                'meaning' => '아직 완성하지 못한 작품',
                'status' => 'draft',
                'theme' => 'love',
                'size_code' => 'standard',
                'back_message' => null,
                'applied_fortune_color_id' => null,
                'applied_frame_id' => $defaultFrameId,
                'preview_file_id' => null,
                'view_count' => 0,
                'published_at' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'user_id' => $user1Id,
                'title' => '연애운 상승 오마모리',
                'meaning' => '좋은 인연을 만나기를 기원합니다',
                'status' => 'published',
                'theme' => 'love',
                'size_code' => 'small',
                'back_message' => '진심은 통한다',
                'applied_fortune_color_id' => $pinkColorId ?: $redColorId,
                'applied_frame_id' => $user1FrameId, // sakura 없으면 기본 프레임
                'preview_file_id' => null,
                'view_count' => 234,
                'published_at' => $now->copy()->subDays(15),
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(15),
            ],
        ];

        foreach ($omamoris as $omamori) {
            if (empty($omamori['applied_frame_id'])) {
                $omamori['applied_frame_id'] = $defaultFrameId;
            }

            $omamoriId = DB::table('omamoris')->insertGetId($omamori);

            // published만 요소 추가
            if (($omamori['status'] ?? null) === 'published') {
                DB::table('omamori_elements')->insert([
                    [
                        'omamori_id' => $omamoriId,
                        'type' => 'text',
                        'layer' => 1,
                        'props' => json_encode([
                            'text' => $omamori['title'],
                            'fontSize' => 24,
                            'fontFamily' => 'Noto Sans JP',
                            'color' => '#000000',
                        ], JSON_UNESCAPED_UNICODE),
                        'transform' => json_encode([
                            'x' => 50,
                            'y' => 30,
                            'rotation' => 0,
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => $omamori['created_at'],
                        'updated_at' => $omamori['updated_at'],
                    ],
                ]);
            }
        }
    }
}