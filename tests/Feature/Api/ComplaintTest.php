<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->user = User::factory()->create(['apikey' => Str::random(32)]);

        // Жалоба на личное сообщение: в ядре без модулей это единственный тип со своей записью
        $this->message = $this->user->sendMessage(User::factory()->create(), 'Спорное сообщение');
    }

    public function testComplaintRequiresToken(): void
    {
        $this->postJson('/api/complaint', [
            'type' => Message::$morphName,
            'id'   => $this->message->id,
        ])->assertStatus(400);
    }

    public function testComplaintIsSaved(): void
    {
        $this->postJson('/api/complaint', [
            'type' => Message::$morphName,
            'id'   => $this->message->id,
        ], $this->headers())->assertStatus(201);

        $this->assertDatabaseHas('spam', [
            'relate_type' => Message::$morphName,
            'relate_id'   => $this->message->id,
            'user_id'     => $this->user->id,
        ]);
    }

    public function testSecondComplaintIsRejected(): void
    {
        $payload = ['type' => Message::$morphName, 'id' => $this->message->id];

        $this->postJson('/api/complaint', $payload, $this->headers())->assertStatus(201);
        $this->postJson('/api/complaint', $payload, $this->headers())->assertStatus(422);

        $this->assertDatabaseCount('spam', 1);
    }

    public function testUnknownRecordIsRejected(): void
    {
        $this->postJson('/api/complaint', [
            'type' => Message::$morphName,
            'id'   => 999999,
        ], $this->headers())->assertStatus(422);
    }

    public function testUnknownTypeIsRejected(): void
    {
        $this->postJson('/api/complaint', [
            'type' => 'nosuchtype',
            'id'   => $this->message->id,
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    public function testComplaintTypesAreListedInConfig(): void
    {
        $types = $this->getJson('/api/config')->assertOk()->json('types.complaint');

        $this->assertContains(Comment::$morphName, $types);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }
}
