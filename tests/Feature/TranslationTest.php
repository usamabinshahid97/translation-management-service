<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->locale = Locale::factory()->create(['code' => 'en', 'name' => 'English']);
        $this->tag = Tag::factory()->create(['name' => 'web']);
    }

    public function test_can_create_translation()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/translations', [
                'key' => 'welcome.message',
                'value' => 'Welcome to our application',
                'locale_code' => 'en',
                'tags' => ['web']
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'key', 'value', 'locale_id', 'created_at', 'updated_at',
                'locale' => ['id', 'code', 'name'],
                'tags' => [['id', 'name']]
            ]);
    }

    public function test_can_retrieve_translations()
    {
        $translation = Translation::factory()->create([
            'key' => 'test.key',
            'value' => 'Test value',
            'locale_id' => $this->locale->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/translations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'key', 'value', 'locale_id',
                        'locale' => ['id', 'code', 'name'],
                        'tags'
                    ]
                ]
            ]);
    }

    public function test_can_search_translations()
    {
        Translation::factory()->create([
            'key' => 'search.test',
            'value' => 'Searchable content',
            'locale_id' => $this->locale->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/translations/search?q=search');

        $response->assertStatus(200);
    }

    public function test_can_export_translations()
    {
        Translation::factory()->create([
            'key' => 'export.test',
            'value' => 'Export value',
            'locale_id' => $this->locale->id
        ]);

        $response = $this->getJson('/api/translations/export?locale=en');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'export.test'
            ]);
    }

    public function test_export_performance()
    {
        $translations = Translation::factory()->count(1000)->create([
            'locale_id' => $this->locale->id
        ]);

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/translations/export?locale=en');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $duration, 'Export should complete in less than 500ms');
    }

    public function test_requires_authentication_for_crud()
    {
        $response = $this->postJson('/api/translations', [
            'key' => 'test.key',
            'value' => 'Test value',
            'locale_code' => 'en'
        ]);

        $response->assertStatus(401);
    }

    public function test_validates_required_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/translations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'value', 'locale_code']);
    }
}