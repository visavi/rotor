<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PictureControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('media_extensions', 'jpg,jpeg,png,gif,webp,mp4');

        $this->user = User::factory()->create(['timebonus' => now()]);
    }

    public function testVideoIsRejected(): void
    {
        // mp4 есть в медиа-расширениях, но аватар из видео не нарезать — раньше это был 500
        $this->actingAs($this->user)
            ->from('/pictures')
            ->post('/pictures', ['photo' => UploadedFile::fake()->create('me.mp4', 100, 'video/mp4')])
            ->assertRedirect('/pictures')
            ->assertSessionHasErrors(['photo' => __('validator.extension')]);

        $this->assertNull($this->user->fresh()->picture);
    }

    public function testSmallPhotoIsRejected(): void
    {
        $this->actingAs($this->user)
            ->post('/pictures', ['photo' => UploadedFile::fake()->image('me.jpg', 50, 50)])
            ->assertSessionHasErrors(['photo' => __('validator.weight_min', ['weight' => 100])]);

        $this->assertNull($this->user->fresh()->picture);
    }

    public function testMissingPhotoIsRejected(): void
    {
        $this->actingAs($this->user)
            ->post('/pictures', [])
            ->assertSessionHasErrors(['photo' => __('validator.image_upload_failed')]);
    }

    public function testDeleteWithoutPhotoFails(): void
    {
        $this->actingAs($this->user)
            ->delete('/pictures/delete')
            ->assertRedirect('profile')
            ->assertSessionHasErrors();
    }
}
