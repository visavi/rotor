<?php

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FlashMessagesTest extends TestCase
{
    public function testMessagesAreGivenAsData(): void
    {
        session()->flash('success', 'Сохранено');
        session()->flash('warning', ['Первое', 'Второе']);

        $this->assertSame(
            ['success' => ['Сохранено'], 'warning' => ['Первое', 'Второе']],
            $this->flashData(),
        );
    }

    public function testValidationErrorsJoinDanger(): void
    {
        session()->flash('danger', 'Ошибка');
        session()->flash('errors', (new ViewErrorBag())->put('default', new MessageBag(['msg' => 'Пустое поле'])));

        $this->assertSame(['danger' => ['Пустое поле', 'Ошибка']], $this->flashData());
    }

    public function testRepeatedMessagesStayList(): void
    {
        // Без переиндексации повтор дал бы дырявые ключи, и JSON стал бы объектом:
        // main.js ждёт массив и упал бы, не дойдя до остальной инициализации
        session()->flash('success', ['Первое', 'Первое', 'Второе']);
        session()->flash('danger', 'Пустое поле');
        session()->flash('errors', (new ViewErrorBag())->put('default', new MessageBag(['msg' => 'Пустое поле'])));

        $this->assertSame(
            ['success' => ['Первое', 'Второе'], 'danger' => ['Пустое поле']],
            $this->flashData(),
        );
    }

    public function testMarkupCannotCloseScript(): void
    {
        session()->flash('danger', '</script><b>');

        $html = (string) $this->view('app/_flash');

        $this->assertStringNotContainsString('</script><b>', $html);
        $this->assertSame(['danger' => ['</script><b>']], $this->flashData());
    }

    public function testMessagesStayWithoutScripts(): void
    {
        session()->flash('success', 'Сохранено');

        $this->view('app/_flash')
            ->assertSee('<noscript>', false)
            ->assertSee('<div class="alert alert-success" role="alert">', false);
    }

    public function testNothingWithoutMessages(): void
    {
        $this->assertSame('', trim((string) $this->view('app/_flash')));
    }

    /**
     * Сообщения из JSON, который читает main.js
     */
    private function flashData(): array
    {
        preg_match('~<script type="application/json" class="js-flash">(.*?)</script>~s', (string) $this->view('app/_flash'), $match);

        return json_decode($match[1] ?? 'null', true) ?? [];
    }
}
