<?php

namespace App\Console\Commands;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTranslations extends Command
{
    protected $signature = 'translations:generate {count=100000 : Number of translations to generate}';
    protected $description = 'Generate test translations for performance testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->info("Generating {$count} translations...");

        $startTime = microtime(true);

        DB::transaction(function () use ($count) {
            $locales = Locale::all();
            $tags = Tag::all();

            if ($locales->isEmpty()) {
                $this->info('Creating default locales...');
                $locales = collect([
                    Locale::create(['code' => 'en', 'name' => 'English', 'is_active' => true]),
                    Locale::create(['code' => 'fr', 'name' => 'French', 'is_active' => true]),
                    Locale::create(['code' => 'es', 'name' => 'Spanish', 'is_active' => true]),
                    Locale::create(['code' => 'de', 'name' => 'German', 'is_active' => true]),
                    Locale::create(['code' => 'it', 'name' => 'Italian', 'is_active' => true]),
                ]);
            }

            if ($tags->isEmpty()) {
                $this->info('Creating default tags...');
                $tags = collect([
                    Tag::create(['name' => 'mobile', 'description' => 'Mobile app translations']),
                    Tag::create(['name' => 'web', 'description' => 'Web app translations']),
                    Tag::create(['name' => 'desktop', 'description' => 'Desktop app translations']),
                    Tag::create(['name' => 'api', 'description' => 'API translations']),
                    Tag::create(['name' => 'email', 'description' => 'Email translations']),
                ]);
            }

            $batchSize = 1000;
            $translationKeys = [
                'welcome.title', 'welcome.message', 'nav.home', 'nav.about', 'nav.contact',
                'btn.save', 'btn.cancel', 'btn.delete', 'btn.edit', 'btn.submit',
                'form.name', 'form.email', 'form.password', 'error.required',
                'success.saved', 'validation.email', 'menu.dashboard', 'status.active'
            ];

            $translationValues = [
                'Welcome', 'Hello World', 'Home', 'About Us', 'Contact',
                'Save', 'Cancel', 'Delete', 'Edit', 'Submit',
                'Name', 'Email', 'Password', 'This field is required',
                'Successfully saved', 'Invalid email format', 'Dashboard', 'Active'
            ];

            for ($i = 0; $i < $count; $i += $batchSize) {
                $batch = [];
                $currentBatchSize = min($batchSize, $count - $i);
                
                for ($j = 0; $j < $currentBatchSize; $j++) {
                    $keyIndex = array_rand($translationKeys);
                    $valueIndex = array_rand($translationValues);
                    $locale = $locales->random();
                    
                    $batch[] = [
                        'key' => $translationKeys[$keyIndex] . '_' . ($i + $j),
                        'value' => $translationValues[$valueIndex] . ' ' . ($i + $j),
                        'locale_id' => $locale->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                Translation::insert($batch);
                
                $this->info("Generated " . ($i + $currentBatchSize) . " translations...");
            }

            $this->info('Attaching tags to translations...');
            $translations = Translation::inRandomOrder()->limit($count / 10)->get();
            foreach ($translations as $translation) {
                $randomTags = $tags->random(rand(1, 3));
                $translation->tags()->attach($randomTags->pluck('id'));
            }
        });

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info("Successfully generated {$count} translations in {$duration} seconds!");
        return Command::SUCCESS;
    }
}