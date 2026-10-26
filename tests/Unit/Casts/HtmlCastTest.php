<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\HtmlCast;
use App\Models\Comment;
use App\Models\User;
use App\Support\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(HtmlCast::class)]
class HtmlCastTest extends TestCase
{
    private array $textFilters;

    protected function setUp(): void
    {
        parent::setUp();

        // Фильтры модулей живут в статике весь прогон: подменяем только их и возвращаем как было
        $this->textFilters = Registry::$textFilters;
        Registry::$textFilters = [];
    }

    protected function tearDown(): void
    {
        Registry::$textFilters = $this->textFilters;

        parent::tearDown();
    }

    public function testNullBecomesEmptyString(): void
    {
        // Пустое поле формы приходит null, а колонка text NOT NULL
        $comment = new Comment(['text' => null]);

        $this->assertSame('', $comment->getAttributes()['text']);
    }

    public function testNullableKeepsNull(): void
    {
        $user = new User(['info' => null]);

        $this->assertNull($user->getAttributes()['info']);
        $this->assertNull($user->info);
    }

    public function testHtmlIsSanitized(): void
    {
        $comment = new Comment(['text' => '<p>текст</p><script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script', $comment->getAttributes()['text']);
        $this->assertStringContainsString('текст', $comment->getAttributes()['text']);
    }

    public function testUnknownTagKeepsText(): void
    {
        $comment = new Comment(['text' => '<h2>Заголовок</h2><p><b>жирный</b> и <i>курсив</i></p><section>блок</section><hr>']);

        $this->assertSame('Заголовок<p>жирный и курсив</p>блок', $comment->getAttributes()['text']);
    }

    public function testScriptIsDroppedWithContent(): void
    {
        $comment = new Comment(['text' => '<p>текст</p><script>alert(1)</script>']);

        $this->assertSame('<p>текст</p>', $comment->getAttributes()['text']);
    }

    public function testStyleKeepsOnlyEditorProperties(): void
    {
        // Стиль без белого списка позволял накрыть страницу своим блоком с фальшивой формой
        $comment = new Comment(['text' => '<p style="text-align: center; position: fixed; inset: 0; z-index: 9999">'
            . '<span style="color: #ff0000; background-color: rgb(255, 255, 0); font-size: 18px; background-image: url(https://evil.example/x.png)">текст</span></p>'
            . '<span style="position:absolute;top:0">скрыто</span>']);

        $this->assertSame(
            '<p style="text-align: center"><span style="color: #ff0000; background-color: rgb(255, 255, 0); font-size: 18px">текст</span></p><span>скрыто</span>',
            $comment->getAttributes()['text'],
        );
    }

    public function testClassKeepsOnlyEditorClasses(): void
    {
        // Утилиты Bootstrap в class накрывали страницу так же, как position: fixed в style
        $comment = new Comment(['text' => '<div class="position-fixed top-0 start-0 w-100 h-100 z-3">'
            . '<a class="stretched-link" href="https://evil.example/login">войти</a></div>'
            . '<div class="hidden video-x">скрыто</div>'
            . '<img class="image d-none" src="/uploads/a.png" alt="">'
            . '<a class="user" href="/users/admin">@admin</a>'
            . '<pre class="code"><code>x</code></pre>'
            . '<details class="spoiler"><summary>s</summary></details>'
            . '<table class="table position-absolute"><tbody><tr><td>1</td></tr></tbody></table>']);

        $this->assertSame(
            '<div><a href="https://evil.example/login">войти</a></div>'
            . '<div class="hidden">скрыто</div>'
            . '<img class="image" src="/uploads/a.png" alt />'
            . '<a class="user" href="/users/admin">@admin</a>'
            . '<pre class="code"><code>x</code></pre>'
            . '<details class="spoiler"><summary>s</summary></details>'
            . '<table class="table"><tbody><tr><td>1</td></tr></tbody></table>',
            $comment->getAttributes()['text'],
        );
    }

    public function testIframeOnlyFromVideoHosts(): void
    {
        $video = '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" allowfullscreen="true" frameborder="0" loading="lazy"></iframe>';

        $comment = new Comment(['text' => '<div class="video">' . $video . '</div>'
            . '<iframe src="https://evil.example/login"></iframe>'
            . '<iframe src="https://youtube.com.evil.example/embed/x"></iframe>'
            . '<iframe src="data:text/html,<b>x</b>"></iframe>']);

        // Чужой iframe убирается целиком, похожий на видеохостинг домен не проходит
        $this->assertSame('<div class="video">' . $video . '</div>', $comment->getAttributes()['text']);
    }

    public function testFiltersApplyOnReadAndKeepOriginal(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('плохо', '***', $text));

        $comment = new Comment(['text' => '<p>плохо</p>']);

        $this->assertSame('<p>***</p>', $comment->text);
        $this->assertSame('<p>плохо</p>', $comment->getAttributes()['text']);
    }

    public function testFiltersSkipMarkup(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('ass', '***', $text));

        $comment = new Comment(['text' => '<p><a href="/pass">class ass</a></p>']);

        $this->assertSame('<p><a href="/pass">cl*** ***</a></p>', $comment->text);
    }
}
