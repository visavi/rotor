<?php

namespace Tests\Feature;

use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        Registry::flush();
    }

    protected function tearDown(): void
    {
        Registry::flush();

        parent::tearDown();
    }

    public function testModuleRowsAreListed(): void
    {
        $this->overrideSetting('test_point', 2);
        $this->overrideSetting('test_money', 100);
        $this->overrideSetting('test_ability', 300);

        Registry::faqReward('Загрузка тестовой записи', 'test_point', 'test_money');
        Registry::faqAbility('Тестовая возможность', 'test_ability');

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Загрузка тестовой записи')
            ->assertSee('+' . plural(100, setting('moneyname')))
            ->assertSee('Тестовая возможность')
            ->assertSee(plural(300, setting('scorename')));
    }

    public function testRowsWithoutAmountAreHidden(): void
    {
        $this->overrideSetting('test_point', 0);

        Registry::faqReward('Загрузка тестовой записи', 'test_point', 'missing_money');
        Registry::faqAbility('Тестовая возможность', 'missing_ability');

        $this->get('/faq')
            ->assertOk()
            ->assertDontSee('Загрузка тестовой записи')
            ->assertDontSee('Тестовая возможность');
    }

    public function testAbilitiesAreSortedByPoint(): void
    {
        $this->overrideSetting('high_ability', 500);
        $this->overrideSetting('low_ability', 10);

        Registry::faqAbility('Дорогая возможность', 'high_ability');
        Registry::faqAbility('Дешёвая возможность', 'low_ability');

        $this->get('/faq')->assertSeeInOrder(['Дешёвая возможность', 'Дорогая возможность']);
    }
}
