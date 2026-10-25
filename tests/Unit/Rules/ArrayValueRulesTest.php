<?php

namespace Tests\Unit\Rules;

use App\Rules\EmailRule;
use App\Rules\LoginRule;
use App\Rules\NotBlacklistedRule;
use App\Rules\PhoneRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Массив в поле (?email[]=) отклоняется ошибкой валидации, а не 500
 */
class ArrayValueRulesTest extends TestCase
{
    public static function rules(): array
    {
        return [
            'email' => [static fn () => new EmailRule(), 'validator.email'],
            'login' => [static fn () => new LoginRule(), 'validator.login'],
            'phone' => [static fn () => new PhoneRule(), 'validator.phone'],
        ];
    }

    /**
     * @param callable(): ValidationRule $rule
     */
    #[DataProvider('rules')]
    public function testArrayFails(callable $rule, string $message): void
    {
        $errors = Validator::make(['field' => ['value']], ['field' => $rule()])->errors()->get('field');

        self::assertSame([__($message)], $errors);
    }

    public function testBlacklistSkipsArray(): void
    {
        $validator = Validator::make(['field' => ['value']], ['field' => new NotBlacklistedRule('email')]);

        self::assertTrue($validator->passes());
    }
}
