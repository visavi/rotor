<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AjaxFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Relation::morphMap([Comment::$morphName => Comment::class]);
    }

    public function testFileArrayIsRejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/ajax/file/upload', [
                'type' => Comment::$morphName,
                'id'   => 0,
                'file' => [UploadedFile::fake()->create('a.txt', 1), UploadedFile::fake()->create('b.txt', 1)],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
