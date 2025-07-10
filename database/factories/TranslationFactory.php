<?php

namespace Database\Factories;

use App\Models\Translation;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition()
    {
        $keys = [
            'welcome.title',
            'welcome.message',
            'nav.home',
            'nav.about',
            'nav.contact',
            'nav.services',
            'nav.products',
            'btn.save',
            'btn.cancel',
            'btn.delete',
            'btn.edit',
            'btn.submit',
            'btn.login',
            'btn.logout',
            'btn.register',
            'form.name',
            'form.email',
            'form.password',
            'form.confirm_password',
            'form.phone',
            'form.address',
            'error.required',
            'error.invalid_email',
            'error.password_mismatch',
            'error.server_error',
            'success.saved',
            'success.deleted',
            'success.updated',
            'success.created',
            'validation.required',
            'validation.email',
            'validation.min',
            'validation.max',
            'notification.new_message',
            'notification.account_created',
            'notification.password_changed',
            'tooltip.help',
            'tooltip.info',
            'tooltip.warning',
            'menu.dashboard',
            'menu.settings',
            'menu.profile',
            'menu.reports',
            'status.active',
            'status.inactive',
            'status.pending',
            'status.completed',
        ];

        return [
            'key' => $this->faker->randomElement($keys),
            'value' => $this->faker->sentence(rand(1, 8)),
            'locale_id' => Locale::inRandomOrder()->first()->id ?? Locale::factory()->create()->id,
        ];
    }
}