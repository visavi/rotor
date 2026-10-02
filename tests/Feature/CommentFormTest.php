<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class CommentFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Relation::morphMap([Comment::$morphName => Comment::class]);

        $this->actingAs(User::factory()->create());
    }

    public function testFormIsCompact(): void
    {
        $this->renderForm(collect())->assertSee('data-compact', false);
    }

    public function testFormIsOpenWithPendingFiles(): void
    {
        $file = new File([
            'relate_type' => Comment::$morphName,
            'path'        => '/uploads/comments/screen.jpg',
            'name'        => 'screen.jpg',
            'size'        => 1024,
            'extension'   => 'jpg',
            'mime_type'   => 'image/jpeg',
        ]);

        $this->renderForm(collect([$file]))->assertDontSee('data-compact', false);
    }

    public function testFormIsOpenWithErrors(): void
    {
        $errors = (new ViewErrorBag())->put('default', new MessageBag(['msg' => 'Ошибка']));

        $this->renderForm(collect(), $errors)->assertDontSee('data-compact', false);
    }

    private function renderForm(Collection $files, ?ViewErrorBag $errors = null): TestView
    {
        return $this->view('app/_comment_form', [
            'action'   => '/comments',
            'comments' => collect(),
            'files'    => $files,
            'errors'   => $errors ?? new ViewErrorBag(),
        ]);
    }
}
