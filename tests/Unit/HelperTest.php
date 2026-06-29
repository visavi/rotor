<?php

namespace Tests\Unit;

use App\Models\Antimat;
use App\Models\BlackList;
use App\Models\Counter;
use App\Models\Counter31;
use App\Models\Notice;
use App\Models\Online;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Сбрасываем процессный memo настроек, чтобы изменения не протекали в другие тесты
        Setting::flush();

        parent::tearDown();
    }

    private function setSetting(string $name, mixed $value): void
    {
        Setting::query()->updateOrCreate(['name' => $name], ['value' => $value]);
        Setting::flush();
    }

    public function testDateFixed(): void
    {
        $date = Date::createFromTimestamp(1117612800);

        self::assertSame('01.06.2005 / 12:00', dateFixed($date));
        self::assertSame('2005-06-01', dateFixed($date, 'Y-m-d'));
        self::assertSame('1 Июня 2005', dateFixed($date, 'j F Y'));
        self::assertSame('1 June 2005', dateFixed($date, 'j F Y', true));
        self::assertSame(dateFixed(now(), 'YmdHi'), dateFixed(null, 'YmdHi'));
    }

    public function testCheck(): void
    {
        self::assertSame('&lt;br&gt;', check('<br>'));
        self::assertSame('&amp;lt;br&amp;gt;', check('&lt;br&gt;'));
        self::assertSame('&lt;br&gt;', check('&lt;br&gt;', false));
        self::assertSame(['&lt;br&gt;', '&lt;html&gt;'], check(['<br>', '<html>']));
        self::assertSame('', check(chr(0) . "\x00" . "\x1A" . chr(226) . chr(128) . chr(174)));
        self::assertSame(' test ', check(' test '));
    }

    public static function intConversionsProvider(): array
    {
        return [
            'positive int'     => [5, 5],
            'string int'       => ['5', 5],
            'false'            => [false, 0],
            'true'             => [true, 1],
            'string with text' => ['1abc', 1],
            'negative int'     => [-1, 1],
            'negative string'  => ['-1', 1],
            'empty array'      => [[], 0],
            'non-empty array'  => [[1], 1],
        ];
    }

    #[DataProvider('intConversionsProvider')]
    public function testInt(mixed $input, int $expected): void
    {
        self::assertSame($expected, int($input));
    }

    public function testIntar(): void
    {
        self::assertSame([5], intar(5));
        self::assertSame([5], intar('5'));
        self::assertSame([0, 1, 33, -1, 0, 0], intar([false, true, '33abc', '-1', 0.01, 'abc']));
        self::assertNull(intar(null));
        self::assertNull(intar(0));
    }

    public static function formatSizeProvider(): array
    {
        return [
            'bytes'     => [5, 2, '5B'],
            'kilobytes' => [1024, 2, '1Kb'],
            'megabytes' => [1048576, 2, '1Mb'],
            'terabytes' => [1099511627776, 2, '1Tb'],
            'precision' => [1000, 4, '0.9766Kb'],
        ];
    }

    #[DataProvider('formatSizeProvider')]
    public function testFormatSize(int $bytes, int $precision, string $expected): void
    {
        self::assertSame($expected, formatSize($bytes, $precision));
    }

    public function testFormatFileSize(): void
    {
        self::assertSame('0B', formatFileSize(UploadedFile::fake()->create('test.txt')->getPathname()));
    }

    public static function formatTimeProvider(): array
    {
        return [
            'zero'   => [0, '0'],
            'minute' => [60, '1 минута'],
            'hour'   => [3600, '1 час'],
            'day'    => [86400, '1 день'],
            'year'   => [86400 * 365, '1 год'],
        ];
    }

    #[DataProvider('formatTimeProvider')]
    public function testFormatTime(int $seconds, string $expected): void
    {
        self::assertSame($expected, formatTime($seconds));
    }

    public function testAntimat(): void
    {
        Antimat::query()->create(['string' => 'xxx']);

        self::assertSame('test', antimat('test'));
        self::assertSame('test***test', antimat('testxxxtest'));
        self::assertSame('тест***тест***', antimat('тестxxxтестxxx'));
    }

    public static function hideMailProvider(): array
    {
        return [
            'long local part' => ['admin@example.com', 'a****@example.com'],
            'two chars'       => ['ab@example.com', 'a*@example.com'],
            'single char'     => ['a@example.com', 'a@example.com'],
            'four chars'      => ['test@test.ru', 't***@test.ru'],
        ];
    }

    #[DataProvider('hideMailProvider')]
    public function testHideMail(string $email, string $expected): void
    {
        self::assertSame($expected, hideMail($email));
    }

    public static function iconsProvider(): array
    {
        return [
            'php'     => ['php', 'fa-regular fa-file-code'],
            'mp3'     => ['mp3', 'fa-regular fa-file-audio'],
            'jpg'     => ['jpg', 'fa-regular fa-file-image'],
            'zip'     => ['zip', 'fa-regular fa-file-archive'],
            'pdf'     => ['pdf', 'fa-regular fa-file-pdf'],
            'unknown' => ['unknown', 'fa-regular fa-file'],
        ];
    }

    #[DataProvider('iconsProvider')]
    public function testIcons(string $ext, string $expectedClass): void
    {
        self::assertStringContainsString($expectedClass, icons($ext)->toHtml());
    }

    public function testTruncateDescription(): void
    {
        self::assertSame('Hello World', truncateDescription('Hello World', 5));
        self::assertSame('Hello', truncateDescription('Hello World', 1, ''));
        self::assertSame('Hello...', truncateDescription('Hello World', 1, '...'));
        self::assertSame('Hello World', truncateDescription("<b>Hello</b>\nWorld", 5));
    }

    public function testFormatNum(): void
    {
        self::assertSame('<span style="color:#00aa00">+5</span>', formatNum(5)->toHtml());
        self::assertSame('<span style="color:#ff0000">-3</span>', formatNum(-3)->toHtml());
        self::assertSame('<span>0</span>', formatNum(0)->toHtml());
    }

    public static function formatShortNumProvider(): array
    {
        return [
            'below threshold' => [999, 999],
            'exact thousand'  => [1000, 1000],
            'kilos'           => [1500, '1.5K'],
            'megas'           => [1500000, '1.5M'],
            'gigas'           => [1500000000, '1.5B'],
            'teras'           => [1500000000000, '1.5T'],
        ];
    }

    #[DataProvider('formatShortNumProvider')]
    public function testFormatShortNum(int $input, int|string $expected): void
    {
        self::assertSame($expected, formatShortNum($input));
    }

    public static function pluralProvider(): array
    {
        return [
            '1 (first form)'                  => [1, ['год', 'года', 'лет'], '1 год'],
            '2 (second form)'                 => [2, ['год', 'года', 'лет'], '2 года'],
            '5 (third form)'                  => [5, ['год', 'года', 'лет'], '5 лет'],
            '11 (exception)'                  => [11, ['год', 'года', 'лет'], '11 лет'],
            '21 (first again)'                => [21, ['год', 'года', 'лет'], '21 год'],
            '100 (third form)'                => [100, ['год', 'года', 'лет'], '100 лет'],
            '1521 (separator, first form)'    => [1521, ['год', 'года', 'лет'], "1\u{202F}521 год"],
            '1000000 (separator, third form)' => [1000000, ['год', 'года', 'лет'], "1\u{202F}000\u{202F}000 лет"],
        ];
    }

    #[DataProvider('pluralProvider')]
    public function testPlural(int $num, array $forms, string $expected): void
    {
        self::assertSame($expected, plural($num, $forms));
    }

    public function testPlural_stringForms(): void
    {
        self::assertSame('1 штука', plural(1, 'штука'));
        self::assertSame('5 лет', plural(5, 'год,года,лет'));
    }

    public static function extensionProvider(): array
    {
        return [
            'simple'       => ['photo.jpg', 'jpg'],
            'double ext'   => ['archive.tar.gz', 'gz'],
            'no extension' => ['noextension', ''],
            'uppercase'    => ['script.PHP', 'PHP'],
        ];
    }

    #[DataProvider('extensionProvider')]
    public function testGetExtension(string $filename, string $expected): void
    {
        self::assertSame($expected, getExtension($filename));
    }

    public static function bodyNameProvider(): array
    {
        return [
            'simple'       => ['photo.jpg', 'photo'],
            'double ext'   => ['archive.tar.gz', 'archive.tar'],
            'no extension' => ['noextension', 'noextension'],
        ];
    }

    #[DataProvider('bodyNameProvider')]
    public function testGetBodyName(string $filename, string $expected): void
    {
        self::assertSame($expected, getBodyName($filename));
    }

    public function testClearCache(): void
    {
        self::assertTrue(clearCache());
        self::assertTrue(clearCache('testKey'));
        self::assertTrue(clearCache(['key1', 'key2']));
    }

    public static function parseVersionProvider(): array
    {
        return [
            'full version'  => ['14.0.0', '14.0.0'],
            'short version' => ['14.0', '14.0.0'],
            'major only'    => ['14', '14.0.0'],
            'with suffix'   => ['14.0.0-beta', '14.0.0'],
            'extra parts'   => ['1.2.3.4', '1.2.3'],
            'empty string'  => ['', '0.0.0'],
        ];
    }

    #[DataProvider('parseVersionProvider')]
    public function testParseVersion(string $version, string $expected): void
    {
        self::assertSame($expected, parseVersion($version));
    }

    public function testUniqueName(): void
    {
        $name = uniqueName();

        self::assertStringNotContainsString('.', $name);
        self::assertNotSame($name, uniqueName());
        self::assertStringEndsWith('.jpg', uniqueName('jpg'));
    }

    public function testAbsolutizeUrls(): void
    {
        config(['app.url' => 'https://site.test']);

        self::assertSame(
            '<a href="https://site.test/page">x</a>',
            absolutizeUrls('<a href="/page">x</a>')
        );
        self::assertSame(
            '<img src="https://site.test/img/a.png">',
            absolutizeUrls('<img src="/img/a.png">')
        );
        self::assertSame(
            '<a href="https://other.test/page">x</a>',
            absolutizeUrls('<a href="https://other.test/page">x</a>')
        );
    }

    public function testDeleteDir(): void
    {
        $dir = sys_get_temp_dir() . '/helper_test_' . uniqid();
        mkdir($dir . '/nested', 0777, true);
        file_put_contents($dir . '/file.txt', 'data');
        file_put_contents($dir . '/nested/inner.txt', 'data');

        deleteDir($dir);

        self::assertDirectoryDoesNotExist($dir);

        deleteDir($dir . '/missing');
    }

    public function testDeleteFile(): void
    {
        $file = sys_get_temp_dir() . '/helper_test_' . uniqid() . '.txt';
        file_put_contents($file, 'data');

        self::assertTrue(deleteFile($file));
        self::assertFileDoesNotExist($file);
        self::assertTrue(deleteFile($file));
    }

    public function testPaginate(): void
    {
        $paginator = paginate([1, 2, 3, 4, 5], 2, ['sort' => 'name']);

        self::assertSame(5, $paginator->total());
        self::assertSame(3, $paginator->lastPage());
        self::assertSame([1, 2], $paginator->items());
        self::assertStringContainsString('sort=name', $paginator->url(2));
    }

    public function testStatsUsers(): void
    {
        User::factory()->create(['created_at' => now()]);
        User::factory()->create(['created_at' => now()->subDays(2)]);

        self::assertSame('2/+1', statsUsers());
    }

    public function testStatsAdmins(): void
    {
        User::factory()->create();
        User::factory()->admin()->create();
        User::factory()->boss()->create();

        self::assertSame(2, statsAdmins());
    }

    public function testStatsBanned(): void
    {
        User::factory()->create(['level' => User::BANNED, 'timeban' => now()->addSeconds(600)]);
        User::factory()->create(['level' => User::BANNED, 'timeban' => now()->subSeconds(600)]);

        self::assertSame(1, statsBanned());
    }

    public function testStatsRegList(): void
    {
        User::factory()->create(['level' => User::PENDED]);

        self::assertSame(1, statsRegList());
    }

    public function testStatsCounts(): void
    {
        self::assertSame(DB::table('spam')->count(), statsSpam());
        self::assertSame(DB::table('banhist')->count(), statsBanHist());
        self::assertSame(DB::table('ban')->count(), statsIpBanned());
        self::assertSame(DB::table('antimat')->count(), statsAntimat());
        self::assertSame(DB::table('stickers')->count(), statsStickers());
    }

    public function testStatsBlacklist(): void
    {
        BlackList::query()->delete();
        self::assertSame('0/0/0', statsBlacklist());

        BlackList::query()->create(['type' => 'login', 'value' => 'spammer', 'user_id' => 1, 'created_at' => now()]);
        BlackList::query()->create(['type' => 'email', 'value' => 'spam@mail.ru', 'user_id' => 1, 'created_at' => now()]);

        self::assertSame('1/1/0', statsBlacklist());
    }

    public function testStatsOnline(): void
    {
        $user = User::factory()->create();

        Online::query()->create(['uid' => md5('user'), 'ip' => '127.0.0.1', 'brow' => 'Chrome 100', 'user_id' => $user->id]);
        Online::query()->create(['uid' => md5('guest'), 'ip' => '127.0.0.2', 'brow' => 'Firefox 100', 'user_id' => null]);

        [$users, $guests, $total] = statsOnline();

        self::assertSame(1, $users);
        self::assertSame(1, $guests);
        self::assertSame(2, $total);
    }

    public function testShowOnline(): void
    {
        $this->setSetting('onlines', 0);
        self::assertNull(showOnline());

        $this->setSetting('onlines', 1);
        self::assertInstanceOf(HtmlString::class, showOnline());
    }

    public function testStatsCounter(): void
    {
        self::assertArrayHasKey('dayhosts', statsCounter());
    }

    public function testStatsWeek(): void
    {
        Counter31::query()->create(['period' => date('Y-m-d 00:00:00'), 'hosts' => 5, 'hits' => 9]);

        $week = statsWeek();

        self::assertCount(1, $week);
        self::assertSame(5, $week->first()->hosts);
    }

    public function testShowCounter(): void
    {
        $this->setSetting('incount', 0);
        self::assertNull(showCounter());
    }

    public function testShowCounterRender(): void
    {
        Counter::query()->delete();
        Counter::query()->create([
            'period'   => date('Y-m-d H:i:s'),
            'allhosts' => 100,
            'allhits'  => 200,
            'dayhosts' => 10,
            'dayhits'  => 20,
            'hosts24'  => 10,
            'hits24'   => 20,
        ]);

        $this->setSetting('incount', 3);

        self::assertInstanceOf(HtmlString::class, showCounter());
    }

    public function testSendNotifyGuest(): void
    {
        sendNotify('<a class="user" href="/users/somebody">@somebody</a>', '/url', 'title');

        self::assertSame(0, DB::table('messages')->count());
    }

    public function testTextNotice(): void
    {
        self::assertSame(__('main.text_missing'), textNotice('missing_type'));

        Notice::query()->create([
            'type'       => 'test',
            'name'       => 'Test',
            'text'       => 'Hello %login%',
            'user_id'    => 1,
            'created_at' => now(),
        ]);

        self::assertSame('Hello <a class="user" href="/users/vasya">@vasya</a>', textNotice('test', ['login' => 'vasya']));
    }

    public function testPerformanceGuest(): void
    {
        self::assertNull(performance());
    }

    public function testSaveErrorLog(): void
    {
        $this->setSetting('errorlog', 1);

        saveErrorLog(404, 'page not found');
        $this->assertDatabaseHas('errors', ['code' => 404, 'message' => 'page not found']);

        saveErrorLog(999, 'unknown code');
        self::assertSame(1, DB::table('errors')->count());
    }

    public function testShowError(): void
    {
        $error = showError('Something failed');

        self::assertInstanceOf(HtmlString::class, $error);
        self::assertStringContainsString('Something failed', (string) $error);
    }

    public function testGetCaptcha(): void
    {
        $this->setSetting('captcha_type', 'graphical');

        self::assertInstanceOf(HtmlString::class, getCaptcha());
    }

    public function testCaptchaVerify(): void
    {
        $this->setSetting('captcha_type', 'graphical');

        request()->setLaravelSession($this->app['session.store']);
        session(['protect' => 'AbC12']);

        request()->merge(['protect' => 'abc12']);
        self::assertTrue(captchaVerify());

        request()->merge(['protect' => 'wrong']);
        self::assertFalse(captchaVerify());
    }

    public function testSetFlash(): void
    {
        setFlash('success', 'Saved');

        self::assertSame('Saved', session('flash.success'));
    }

    public function testInputHelpers(): void
    {
        self::assertSame('fallback', getInput('field', 'fallback'));

        setInput(['field' => 'value', 'nested' => ['key' => 'deep']]);

        self::assertSame('value', getInput('field'));
        self::assertSame('deep', getInput('nested.key'));
        self::assertNull(getInput('missing'));
    }

    public function testErrorHelpers(): void
    {
        self::assertSame('', hasError('field'));
        self::assertNull(textError('field'));

        $errors = new ViewErrorBag();
        $errors->put('default', new MessageBag(['name' => ['Name is required']]));
        session(['errors' => $errors]);

        self::assertSame(' is-invalid', hasError('name'));
        self::assertSame(' is-valid', hasError('other'));
        self::assertSame('Name is required', textError('name'));
    }

    public function testSendMail(): void
    {
        Mail::fake();

        self::assertTrue(sendMail('mailer.default', [
            'to'      => 'user@example.com',
            'subject' => 'Test subject',
            'text'    => 'Test text',
        ]));
    }

    public function testRenderHtml(): void
    {
        $html = renderHtml('Hello world');

        self::assertInstanceOf(HtmlString::class, $html);
        self::assertStringContainsString('Hello world', (string) $html);
    }

    public function testRenderText(): void
    {
        $text = renderText('Hello world');

        self::assertInstanceOf(HtmlString::class, $text);
        self::assertStringContainsString('Hello world', (string) $text);
    }

    public function testGetIp(): void
    {
        self::assertSame('127.0.0.1', getIp());
    }

    public function testGetBrowser(): void
    {
        $browser = getBrowser();

        self::assertNotSame('', $browser);
        self::assertLessThanOrEqual(25, mb_strlen($browser));
    }

    public function testIsAdmin(): void
    {
        self::assertFalse(isAdmin());

        $this->actingAs(User::factory()->create());
        self::assertFalse(isAdmin());

        $this->actingAs(User::factory()->admin()->create());
        self::assertTrue(isAdmin());
        self::assertFalse(isAdmin(User::BOSS));

        $this->actingAs(User::factory()->boss()->create());
        self::assertTrue(isAdmin(User::BOSS));
    }

    public function testGetUserByLogin(): void
    {
        $user = User::factory()->create();

        self::assertTrue($user->is(getUserByLogin($user->login)));
        self::assertNull(getUserByLogin('missing_login'));
    }

    public function testGetUserByLoginOrEmail(): void
    {
        $user = User::factory()->create();

        self::assertTrue($user->is(getUserByLoginOrEmail($user->login)));
        self::assertTrue($user->is(getUserByLoginOrEmail($user->email)));
        self::assertNull(getUserByLoginOrEmail('missing_login'));
    }

    public function testGetUserGuest(): void
    {
        self::assertNull(getUser());
        self::assertNull(getUser('id'));
    }

    public function testImageBase64(): void
    {
        $path = sys_get_temp_dir() . '/helper_test_' . uniqid() . '.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));

        $img = (string) imageBase64($path);

        self::assertStringContainsString('data:image/png;base64,', $img);
        self::assertStringContainsString('class="img-fluid"', $img);
        self::assertStringContainsString('alt="' . basename($path) . '"', $img);

        $custom = (string) imageBase64($path, ['class' => 'thumb', 'alt' => 'Picture', 'width' => 10]);

        self::assertStringContainsString('class="thumb"', $custom);
        self::assertStringContainsString('alt="Picture"', $custom);
        self::assertStringContainsString('width="10"', $custom);

        unlink($path);
    }

    public function testGetQueryLog(): void
    {
        DB::enableQueryLog();
        User::query()->where('login', 'abc')->get();

        $log = getQueryLog();

        self::assertNotEmpty($log);
        self::assertStringContainsString("'abc'", end($log)['query']);
    }

    public function testSetting(): void
    {
        self::assertIsArray(setting());
        self::assertSame('fallback', setting('missing_key', 'fallback'));

        $this->setSetting('errorlog', 1);
        self::assertSame(1, setting('errorlog'));
    }

    public function testGetAvailableThemes(): void
    {
        self::assertContains('default', getAvailableThemes());
    }

    public function testGetAvailableLanguages(): void
    {
        $languages = getAvailableLanguages();

        self::assertContains('ru', $languages);
        self::assertContains('en', $languages);
    }
}
