<?php

namespace Tests\Feature\Omamori;

use App\Models\File;
use App\Models\Omamori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * POST /api/v1/omamoris/{omamoriId}/export
 */
class OmamoriExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 본인 오마모리 export 성공
     *
     * @return void
     */
    public function test_export_success(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create([
            'status' => 'draft',
        ]);

        Sanctum::actingAs($user);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/export", [
            'format' => 'png',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.omamori_id', $omamori->id)
            ->assertJsonPath('data.content_type', 'image/png')
            ->assertJsonStructure([
                'data' => [
                    'file_id',
                    'omamori_id',
                    'download_url',
                    'file_key',
                    'content_type',
                    'size_bytes',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('files', [
            'user_id' => $user->id,
            'omamori_id' => $omamori->id,
            'purpose' => 'render_output',
            'visibility' => 'private',
        ]);

        // download_url이 DB url과 매칭되는지(최소 검증)
        $fileId = $res->json('data.file_id');
        /** @var File $file */
        $file = File::query()->findOrFail($fileId);
        $this->assertSame($file->url, $res->json('data.download_url'));
    }

    /**
     * 타인 오마모리 export는 403
     *
     * @return void
     */
    public function test_export_forbidden_when_other_users_omamori(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $omamori = Omamori::factory()->for($owner)->create();

        Sanctum::actingAs($other);

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/export");

        $res->assertStatus(403);
    }

    /**
     * 인증 없이 export는 401
     *
     * @return void
     */
    public function test_export_unauthorized(): void
    {
        $user = User::factory()->create();
        $omamori = Omamori::factory()->for($user)->create();

        $res = $this->postJson("/api/v1/omamoris/{$omamori->id}/export");

        $res->assertStatus(401);
    }
}