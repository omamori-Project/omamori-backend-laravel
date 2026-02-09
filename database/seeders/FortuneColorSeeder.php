<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FortuneColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'code' => 'red_passion',
                'name' => '정열의 빨강',
                'hex' => '#FF0000',
                'category' => 'energy',
                'short_meaning' => '열정과 용기를 불러일으킵니다',
                'meaning' => '빨간색은 강한 에너지와 열정을 상징합니다. 새로운 도전을 시작하거나 용기가 필요할 때 힘을 줍니다.',
                'tips' => json_encode([
                    '중요한 프레젠테이션 전에',
                    '새로운 도전을 시작할 때',
                    '운동이나 경쟁에서 승리하고 싶을 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'blue_peace',
                'name' => '평온의 파랑',
                'hex' => '#0000FF',
                'category' => 'calm',
                'short_meaning' => '마음의 평화와 안정을 가져다줍니다',
                'meaning' => '파란색은 평온함과 신뢰를 상징합니다. 스트레스가 많을 때나 마음의 안정이 필요할 때 도움을 줍니다.',
                'tips' => json_encode([
                    '시험이나 면접 전에',
                    '불안감을 느낄 때',
                    '집중력이 필요한 작업을 할 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'green_growth',
                'name' => '성장의 초록',
                'hex' => '#00FF00',
                'category' => 'growth',
                'short_meaning' => '건강과 성장의 기운을 담고 있습니다',
                'meaning' => '초록색은 생명력과 재생을 상징합니다. 건강 회복이나 새로운 시작을 위한 에너지를 제공합니다.',
                'tips' => json_encode([
                    '건강 회복을 위해',
                    '새로운 습관을 만들 때',
                    '자연과 교감하고 싶을 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'yellow_joy',
                'name' => '기쁨의 노랑',
                'hex' => '#FFFF00',
                'category' => 'happiness',
                'short_meaning' => '밝은 에너지와 즐거움을 선사합니다',
                'meaning' => '노란색은 햇살처럼 밝고 긍정적인 에너지를 상징합니다. 우울할 때나 기운이 필요할 때 활력을 줍니다.',
                'tips' => json_encode([
                    '우울한 기분을 떨쳐낼 때',
                    '창의적인 아이디어가 필요할 때',
                    '사람들과의 모임에서',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'purple_wisdom',
                'name' => '지혜의 보라',
                'hex' => '#800080',
                'category' => 'wisdom',
                'short_meaning' => '통찰력과 직관을 키워줍니다',
                'meaning' => '보라색은 영적인 깊이와 지혜를 상징합니다. 명상이나 깊은 사색이 필요할 때 도움을 줍니다.',
                'tips' => json_encode([
                    '중요한 결정을 앞두고',
                    '명상이나 요가를 할 때',
                    '예술적 영감이 필요할 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pink_love',
                'name' => '사랑의 분홍',
                'hex' => '#FFC0CB',
                'category' => 'love',
                'short_meaning' => '따뜻한 사랑과 애정을 담고 있습니다',
                'meaning' => '분홍색은 부드러운 사랑과 배려를 상징합니다. 인간관계에서 따뜻함을 나누고 싶을 때 좋습니다.',
                'tips' => json_encode([
                    '연인이나 가족과의 시간에',
                    '자기 자신을 사랑하고 싶을 때',
                    '감사의 마음을 전할 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'gold_fortune',
                'name' => '행운의 금색',
                'hex' => '#FFD700',
                'category' => 'fortune',
                'short_meaning' => '풍요와 성공의 기운을 불러옵니다',
                'meaning' => '금색은 부와 성공을 상징합니다. 재정적 풍요나 목표 달성을 원할 때 좋은 에너지를 줍니다.',
                'tips' => json_encode([
                    '사업이나 투자 시작 전에',
                    '승진이나 계약을 앞두고',
                    '금전적 풍요를 바랄 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'white_purity',
                'name' => '순수의 하양',
                'hex' => '#FFFFFF',
                'category' => 'purity',
                'short_meaning' => '깨끗함과 새로운 시작을 상징합니다',
                'meaning' => '하얀색은 순수함과 새로운 시작을 상징합니다. 마음을 비우고 새롭게 시작하고 싶을 때 도움을 줍니다.',
                'tips' => json_encode([
                    '새해나 새로운 프로젝트 시작 시',
                    '마음의 정화가 필요할 때',
                    '깨끗한 마음으로 출발하고 싶을 때',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('fortune_colors')->insert($colors);
    }
}