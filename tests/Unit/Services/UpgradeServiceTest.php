<?php

namespace Tests\Unit\Services;

use App\Models\Module;
use App\Services\GithubService;
use App\Services\UpgradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UpgradeService::class)]
class UpgradeServiceTest extends TestCase
{
    use RefreshDatabase;

    private UpgradeService $upgrade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->upgrade = new UpgradeService();
    }

    public function testNextMajorIsHiddenUntilMajorLineIsFinished(): void
    {
        $github = $this->releases([
            ROTOR_VERSION,
            $this->nextPatch(),
            $this->nextMinor(),
            $this->nextMajor(),
        ]);

        $versions = array_map(static fn (array $r) => ltrim($r['tag_name'], 'v'), $this->upgrade->getNewReleases($github));

        // Сайт не на вершине своей линии — мажор в списке не появляется
        $this->assertSame([$this->nextPatch(), $this->nextMinor()], $versions);
        $this->assertSame($this->nextMinor(), $this->upgrade->requiredBeforeMajor($github));
        $this->assertFalse($this->upgrade->canUpgradeTo('v' . $this->nextMajor(), $github));
        $this->assertTrue($this->upgrade->canUpgradeTo('v' . $this->nextMinor(), $github));
    }

    public function testNextMajorIsAllowedFromLatestOfMajorLine(): void
    {
        $github = $this->releases([ROTOR_VERSION, $this->nextMajor()]);

        $versions = array_map(static fn (array $r) => ltrim($r['tag_name'], 'v'), $this->upgrade->getNewReleases($github));

        $this->assertSame([$this->nextMajor()], $versions);
        $this->assertNull($this->upgrade->requiredBeforeMajor($github));
        $this->assertTrue($this->upgrade->canUpgradeTo('v' . $this->nextMajor(), $github));
    }

    public function testWithoutMajorReleaseNothingIsBlocked(): void
    {
        $github = $this->releases([ROTOR_VERSION, $this->nextMinor()]);

        $this->assertNull($this->upgrade->requiredBeforeMajor($github));
        $this->assertCount(1, $this->upgrade->getNewReleases($github));
    }

    public function testReleaseWithoutAssetsIsSkipped(): void
    {
        $github = Mockery::mock(GithubService::class);
        $github->shouldReceive('getLatestReleases')->andReturn([
            ['tag_name' => 'v' . $this->nextMinor(), 'assets' => []],
            ['tag_name' => 'v' . $this->nextPatch(), 'assets' => [['name' => 'rotor.zip']]],
        ]);

        $versions = array_map(static fn (array $r) => ltrim($r['tag_name'], 'v'), $this->upgrade->getNewReleases($github));

        $this->assertSame([$this->nextPatch()], $versions);
    }

    public function testOutdatedModulesListedForNextMajorOnly(): void
    {
        // Модуль есть на диске, его requires относится к текущему мажору
        Module::query()->create(['name' => 'Forum', 'version' => '1.0.10', 'active' => 1]);

        $this->assertSame([], $this->upgrade->outdatedModules('v' . $this->nextMinor()));

        $outdated = $this->upgrade->outdatedModules('v' . $this->nextMajor());

        $this->assertCount(1, $outdated);
        // requires модуля отстаёт от целевого мажора — этим он и попал в список
        $this->assertTrue(version_compare($outdated[0]['requires'], $this->nextMajor(), '<'));
    }

    public function testUnknownModuleDirectoryIsSkipped(): void
    {
        Module::query()->create(['name' => 'NoSuchModule', 'version' => '1.0.0', 'active' => 1]);

        $this->assertSame([], $this->upgrade->outdatedModules('v' . $this->nextMajor()));
    }

    private function releases(array $versions): GithubService
    {
        $github = Mockery::mock(GithubService::class);
        $github->shouldReceive('getLatestReleases')->andReturn(array_map(
            static fn (string $version) => ['tag_name' => 'v' . $version, 'assets' => [['name' => 'rotor.zip']]],
            $versions,
        ));

        return $github;
    }

    private function nextPatch(): string
    {
        [$major, $minor, $patch] = explode('.', ROTOR_VERSION);

        return $major . '.' . $minor . '.' . ($patch + 1);
    }

    private function nextMinor(): string
    {
        [$major, $minor] = explode('.', ROTOR_VERSION);

        return $major . '.' . ($minor + 1) . '.0';
    }

    private function nextMajor(): string
    {
        [$major] = explode('.', ROTOR_VERSION);

        return ($major + 1) . '.0.0';
    }
}
