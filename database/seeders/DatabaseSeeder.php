<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Categorías típicas de pollería
        $categories = [
            ['name' => 'Pollo a la brasa', 'description' => 'Nuestro clásico pollo a la brasa jugoso y dorado.'],
            ['name' => 'Pollo broaster', 'description' => 'Pollo frito crujiente estilo broaster.'],
            ['name' => 'Porciones', 'description' => 'Acompañamientos: papas, ensaladas, arroz, etc.'],
            ['name' => 'Bebidas', 'description' => 'Gaseosas, chicha morada y más.'],
            ['name' => 'Salsas y extras', 'description' => 'Ají, mayonesa, ketchup y más.'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // Productos de ejemplo
        $products = [
            [
                'name' => '1 Pollo a la brasa',
                'description' => 'Pollo entero a la brasa con papas y ensalada.',
                'price' => 60.00,
                'stock' => 10,
                'category_id' => Category::where('name', 'Pollo a la brasa')->first()->id,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => '1/2 Pollo a la brasa',
                'description' => 'Medio pollo a la brasa con papas y ensalada.',
                'price' => 35.00,
                'stock' => 15,
                'category_id' => Category::where('name', 'Pollo a la brasa')->first()->id,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => '1/4 Pollo a la brasa',
                'description' => 'Cuarto de pollo a la brasa con papas y ensalada.',
                'price' => 20.00,
                'stock' => 20,
                'category_id' => Category::where('name', 'Pollo a la brasa')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Pollo broaster entero',
                'description' => 'Pollo broaster entero con papas.',
                'price' => 65.00,
                'stock' => 8,
                'category_id' => Category::where('name', 'Pollo broaster')->first()->id,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Papas fritas',
                'description' => 'Porción grande de papas fritas crocantes.',
                'price' => 10.00,
                'stock' => 30,
                'category_id' => Category::where('name', 'Porciones')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Ensalada fresca',
                'description' => 'Ensalada de la casa con lechuga, tomate y zanahoria.',
                'price' => 8.00,
                'stock' => 25,
                'category_id' => Category::where('name', 'Porciones')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Gaseosa 1.5L',
                'description' => 'Gaseosa grande para compartir.',
                'price' => 9.00,
                'stock' => 20,
                'category_id' => Category::where('name', 'Bebidas')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Chicha morada',
                'description' => 'Refresco tradicional peruano.',
                'price' => 7.00,
                'stock' => 18,
                'category_id' => Category::where('name', 'Bebidas')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Ají casero',
                'description' => 'Ají picante tradicional.',
                'price' => 2.00,
                'stock' => 50,
                'category_id' => Category::where('name', 'Salsas y extras')->first()->id,
                'is_active' => true,
                'is_featured' => false
            ],
        ];
        foreach ($products as $prod) {
            Product::firstOrCreate(['name' => $prod['name']], $prod);
        }

        // Ejecutar el seeder del menú de pollería
        $this->call([
            MenuSeeder::class
        ]);
    }
}
