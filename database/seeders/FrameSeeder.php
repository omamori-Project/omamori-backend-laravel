<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrameSeeder extends Seeder
{
    public function run(): void
    {
        $frames = [
            [
                'name' => '전통 목재 프레임',
                'frame_key' => 'traditional_wood',
                'preview_url' => '/assets/frames/traditional_wood_preview.png',
                'asset_file_id' => null, // 실제로는 files 테이블 참조
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '일본 전통 목재 느낌의 프레임',
                    'style' => 'traditional',
                    'border_width' => 10,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '금박 프레임',
                'frame_key' => 'gold_leaf',
                'preview_url' => '/assets/frames/gold_leaf_preview.png',
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '화려한 금박 장식 프레임',
                    'style' => 'luxury',
                    'border_width' => 15,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '벚꽃 프레임',
                'frame_key' => 'sakura',
                'preview_url' => '/assets/frames/sakura_preview.png',
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '봄의 벚꽃이 피어나는 프레임',
                    'style' => 'seasonal',
                    'border_width' => 12,
                    'season' => 'spring',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '단풍 프레임',
                'frame_key' => 'autumn_leaves',
                'preview_url' => '/assets/frames/autumn_leaves_preview.png',
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '가을 단풍잎이 장식된 프레임',
                    'style' => 'seasonal',
                    'border_width' => 12,
                    'season' => 'autumn',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '심플 블랙 프레임',
                'frame_key' => 'simple_black',
                'preview_url' => '/assets/frames/simple_black_preview.png',
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '깔끔한 검은색 테두리 프레임',
                    'style' => 'modern',
                    'border_width' => 5,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '심플 화이트 프레임',
                'frame_key' => 'simple_white',
                'preview_url' => '/assets/frames/simple_white_preview.png',
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '깔끔한 흰색 테두리 프레임',
                    'style' => 'modern',
                    'border_width' => 5,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('frames')->insert($frames);
    }
}