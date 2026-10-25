<?php

namespace Tests\Feature\Admin;

use App\Models\Sticker;
use App\Models\StickersCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StickerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_sticker']);
    }

    private function makeCategory(string $name = 'Тестовая категория'): StickersCategory
    {
        return StickersCategory::factory()->create([
            'name'       => $name,
            'created_at' => now(),
        ]);
    }

    private function makeSticker(StickersCategory $category, string $code = 'testcode'): Sticker
    {
        return Sticker::factory()->create([
            'category_id' => $category->id,
            'name'        => '/uploads/stickers/nonexistent-test.gif',
            'code'        => $code,
        ]);
    }

    public function testIndexShowsCategories(): void
    {
        $this->makeCategory();

        $this->actingAs($this->admin)
            ->get('/admin/stickers')
            ->assertOk()
            ->assertSee('Тестовая категория');
    }

    public function testCategoryShowsStickers(): void
    {
        $category = $this->makeCategory();
        $this->makeSticker($category);

        $this->actingAs($this->admin)
            ->get('/admin/stickers/' . $category->id)
            ->assertOk()
            ->assertSee('testcode');
    }

    public function testCategoryMissingReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/stickers/999999')
            ->assertNotFound();
    }

    public function testCreateCategory(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/create', ['name' => 'Новая категория']);

        $this->assertDatabaseHas('stickers_categories', ['name' => 'Новая категория']);

        $category = StickersCategory::query()->where('name', 'Новая категория')->firstOrFail();

        $response->assertRedirect('admin/stickers/' . $category->id);
        $response->assertSessionHas('success');
    }

    public function testCreateCategoryWithShortNameFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/stickers')
            ->post('/admin/stickers/create', ['name' => 'ab']);

        $this->assertDatabaseMissing('stickers_categories', ['name' => 'ab']);

        $response->assertRedirect('admin/stickers');
        $response->assertSessionHasErrors('name');
    }

    public function testEditCategory(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/edit/' . $category->id, ['name' => 'Изменённая категория']);

        $response->assertRedirect('admin/stickers');

        $this->assertDatabaseHas('stickers_categories', [
            'id'   => $category->id,
            'name' => 'Изменённая категория',
        ]);

        $response->assertSessionHas('success');
    }

    public function testEditCategoryWithShortNameFails(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/edit/' . $category->id, ['name' => 'ab']);

        $this->assertDatabaseHas('stickers_categories', [
            'id'   => $category->id,
            'name' => 'Тестовая категория',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function testEditMissingCategoryReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/stickers/edit/999999')
            ->assertNotFound();
    }

    public function testDeleteEmptyCategory(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/stickers/delete/' . $category->id);

        $response->assertRedirect('admin/stickers');

        $this->assertDatabaseMissing('stickers_categories', ['id' => $category->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteCategoryWithStickersFails(): void
    {
        $category = $this->makeCategory();
        $this->makeSticker($category);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/stickers/delete/' . $category->id);

        $response->assertRedirect('admin/stickers');

        $this->assertDatabaseHas('stickers_categories', ['id' => $category->id]);

        $response->assertSessionHasErrors();
    }

    public function testEditSticker(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/edit/' . $sticker->id, [
                'code' => 'newcode',
                'cid'  => $category->id,
            ]);

        $response->assertRedirect('admin/stickers/' . $category->id . '?page=1');

        $this->assertDatabaseHas('stickers', [
            'id'   => $sticker->id,
            'code' => 'newcode',
        ]);

        $response->assertSessionHas('success');
    }

    public function testEditStickerIsLowercased(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);

        $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/edit/' . $sticker->id, [
                'code' => 'NewCode',
                'cid'  => $category->id,
            ]);

        // Сравниваем в PHP: у таблицы case-insensitive collation
        $this->assertSame('newcode', Sticker::query()->whereKey($sticker->id)->value('code'));
    }

    public function testEditStickerWithDuplicateCodeFails(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);
        $this->makeSticker($category, 'othercode');

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/edit/' . $sticker->id, [
                'code' => 'othercode',
                'cid'  => $category->id,
            ]);

        $this->assertDatabaseHas('stickers', [
            'id'   => $sticker->id,
            'code' => 'testcode',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function testEditStickerWithUnknownCategoryFails(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/edit/' . $sticker->id, [
                'code' => 'newcode',
                'cid'  => 999999,
            ]);

        $this->assertDatabaseHas('stickers', [
            'id'   => $sticker->id,
            'code' => 'testcode',
        ]);

        $response->assertSessionHasErrors(['cid' => __('stickers.category_not_exist')]);
    }

    public function testEditMissingStickerReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/stickers/sticker/edit/999999')
            ->assertNotFound();
    }

    public function testDeleteSticker(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/stickers/sticker/delete/' . $sticker->id);

        $response->assertRedirect('admin/stickers/' . $category->id . '?page=1');

        $this->assertDatabaseMissing('stickers', ['id' => $sticker->id]);

        $response->assertSessionHas('success');
    }

    public function testCreateSticker(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'     => $category->id,
                'code'    => 'NewSticker',
                'sticker' => UploadedFile::fake()->image('sticker.png', 32, 32),
            ]);

        $response->assertRedirect('admin/stickers/' . $category->id);
        $response->assertSessionHas('success');

        $sticker = Sticker::query()->where('code', 'newsticker')->first();

        $this->assertNotNull($sticker);
        $this->assertSame($category->id, $sticker->category_id);
        $this->assertFileExists(public_path($sticker->name));
    }

    public function testCreateStickerWithArrayCodeFails(): void
    {
        $category = $this->makeCategory();

        $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'     => $category->id,
                'code'    => ['NewSticker'],
                'sticker' => UploadedFile::fake()->image('sticker.png', 32, 32),
            ])
            ->assertSessionHasErrors('code');
    }

    public function testCreateStickerWithoutFileFails(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'  => $category->id,
                'code' => 'nofile',
            ]);

        $response->assertSessionHasErrors(['sticker' => __('validator.image_upload_failed')]);

        $this->assertDatabaseMissing('stickers', ['code' => 'nofile']);
    }

    public function testCreateStickerWithWrongExtensionFails(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'     => $category->id,
                'code'    => 'script',
                'sticker' => UploadedFile::fake()->create('sticker.php', 1, 'image/png'),
            ]);

        $response->assertSessionHasErrors(['sticker' => __('validator.extension')]);

        $this->assertDatabaseMissing('stickers', ['code' => 'script']);
    }

    public function testCreateStickerTooBigImageFails(): void
    {
        $category = $this->makeCategory();
        $max = (int) setting('stickermaxweight');

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'     => $category->id,
                'code'    => 'bigone',
                'sticker' => UploadedFile::fake()->image('sticker.png', $max + 1, 32),
            ]);

        $response->assertSessionHasErrors(['sticker' => __('validator.weight_max', ['weight' => $max])]);

        $this->assertDatabaseMissing('stickers', ['code' => 'bigone']);
    }

    public function testCreateStickerWithDuplicateCodeFails(): void
    {
        $category = $this->makeCategory();
        $this->makeSticker($category, 'taken');

        $response = $this->actingAs($this->admin)
            ->post('/admin/stickers/sticker/create', [
                'cid'     => $category->id,
                'code'    => 'TAKEN',
                'sticker' => UploadedFile::fake()->image('sticker.png', 32, 32),
            ]);

        $response->assertSessionHasErrors(['code' => __('stickers.sticker_exists')]);

        $this->assertSame(1, Sticker::query()->where('code', 'taken')->count());
    }

    public function testCreateStickerKeepsSelectedCategoryOnError(): void
    {
        $this->makeCategory('Первая');
        $second = $this->makeCategory('Вторая');

        $this->actingAs($this->admin)
            ->from('/admin/stickers/sticker/create')
            ->post('/admin/stickers/sticker/create', [
                'cid'  => $second->id,
                'code' => 'x',
            ]);

        $this->actingAs($this->admin)
            ->get('/admin/stickers/sticker/create')
            ->assertOk()
            ->assertSee('<option value="' . $second->id . '" selected>', false);
    }

    public function testDeleteStickerOfDeletedCategoryRedirects(): void
    {
        $category = $this->makeCategory();
        $sticker = $this->makeSticker($category);

        StickersCategory::query()->whereKey($category->id)->delete();

        $this->actingAs($this->admin)
            ->delete('/admin/stickers/sticker/delete/' . $sticker->id)
            ->assertRedirect('admin/stickers/' . $category->id . '?page=1');

        $this->assertDatabaseMissing('stickers', ['id' => $sticker->id]);
    }
}
