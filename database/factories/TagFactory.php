<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        $tags = [
            'mobile' => 'Mobile application translations',
            'web' => 'Web application translations',
            'desktop' => 'Desktop application translations',
            'api' => 'API response translations',
            'email' => 'Email template translations',
            'ui' => 'User interface translations',
            'error' => 'Error message translations',
            'success' => 'Success message translations',
            'navigation' => 'Navigation menu translations',
            'form' => 'Form field translations',
            'button' => 'Button text translations',
            'label' => 'Label translations',
            'tooltip' => 'Tooltip translations',
            'validation' => 'Validation message translations',
            'notification' => 'Notification translations',
        ];

        $name = $this->faker->randomElement(array_keys($tags));
        
        return [
            'name' => $name,
            'description' => $tags[$name],
        ];
    }
}