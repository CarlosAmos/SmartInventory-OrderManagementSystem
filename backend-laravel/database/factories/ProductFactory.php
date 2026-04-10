<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adjectives = [
            'Ultimate', 'Premium', 'Professional', 'Advanced', 'Elite',
            'Super', 'Mega', 'Hyper', 'Turbo', 'Quantum',
            'Digital', 'Smart', 'Pro', 'Max', 'Ultra',
            'Lightning', 'Thunder', 'Cosmic', 'Atomic', 'Blazing'
        ];

        $nouns = [
            'Widget', 'Gadget', 'Device', 'Tool', 'Gizmo',
            'Contraption', 'Apparatus', 'Doohickey', 'Thingamajig', 'Whatchamacallit',
            'Component', 'Module', 'Unit', 'System', 'Processor',
            'Generator', 'Catalyst', 'Enhancer', 'Optimizer', 'Accelerator'
        ];

        $suffixes = [
            '3000', 'XL', 'Pro', 'Plus', 'Max', 'Elite',
            '2.0', 'Turbo', 'Ultimate', 'Premium', 'Deluxe',
            'HD', '4K', 'X', 'Z', 'Omega'
        ];

        $adjective = fake()->randomElement($adjectives);
        $noun = fake()->randomElement($nouns);
        $suffix = fake()->randomElement($suffixes);

        return [
            'name' => "{$adjective} {$noun} {$suffix}",
            'sku' => strtoupper(fake()->bothify('PC-???-####')),
            'price' => fake()->randomFloat(2, 29.99, 1999.99),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
        ];
    }
}
