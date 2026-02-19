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
                'preview_path' => 'frames/traditional_wood_preview.png',
                'is_default' => true,
                'asset_file_id' => null, 
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
                'preview_path' => 'frames/gold_leaf_preview.png',
                'is_default' => false,
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
                'name' => '은하수 프레임',
                'frame_key' => 'silver_stars',
                'preview_path' => 'frames/silver_stars_preview.png',
                'is_default' => false,
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '반짝이는 별 장식 프레임',
                    'style' => 'fantasy',
                    'border_width' => 12,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '핑크 리본 프레임',
                'frame_key' => 'pink_ribbon',
                'preview_path' => 'frames/pink_ribbon_preview.png',
                'is_default' => false,
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '귀여운 리본 스타일 프레임',
                    'style' => 'cute',
                    'border_width' => 10,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '푸른 물결 프레임',
                'frame_key' => 'blue_wave',
                'preview_path' => 'frames/blue_wave_preview.png',
                'is_default' => false,
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '차분한 물결 무늬 프레임',
                    'style' => 'calm',
                    'border_width' => 9,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '초록 잎사귀 프레임',
                'frame_key' => 'green_leaf',
                'preview_path' => 'frames/green_leaf_preview.png',
                'is_default' => false,
                'asset_file_id' => null,
                'is_active' => true,
                'meta' => json_encode([
                    'description' => '싱그러운 잎사귀 느낌의 프레임',
                    'style' => 'nature',
                    'border_width' => 8,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // upsert: frame_key 기준
        foreach ($frames as $frame) {
            DB::table('frames')->updateOrInsert(
                ['frame_key' => $frame['frame_key']],
                $frame
            );
        }
    }
}