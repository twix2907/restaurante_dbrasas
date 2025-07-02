<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear categorías si no existen
        $comidasCategory = Category::firstOrCreate(
            ['name' => 'Comidas'],
            [
                'description' => 'Platos principales de pollo y acompañamientos',
                'image' => 'images/categories/comidas.jpg',
                'status' => 'active'
            ]
        );

        $bebidasCategory = Category::firstOrCreate(
            ['name' => 'Bebidas'],
            [
                'description' => 'Refrescos, jugos y bebidas tradicionales',
                'image' => 'images/categories/bebidas.jpg',
                'status' => 'active'
            ]
        );

        // Productos de comidas
        $comidas = [
            [
                'name' => 'Pollo a la Brasa',
                'description' => 'Pollo marinado y asado a la brasa con hierbas aromáticas, servido con papas fritas y ensalada',
                'price' => 25.00,
                'stock' => 50,
                'unit' => '1/4 Pollo',
                'image' => 'images/products/pollo-brasa.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Pollo Broaster',
                'description' => 'Pollo frito crujiente con especias especiales, servido con papas fritas',
                'price' => 18.00,
                'stock' => 40,
                'unit' => '4 Piezas',
                'image' => 'images/products/pollo-broaster.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Alitas BBQ',
                'description' => 'Alitas de pollo bañadas en salsa BBQ ahumada, servidas con aderezo ranch',
                'price' => 22.00,
                'stock' => 30,
                'unit' => '6 Piezas',
                'image' => 'images/products/alitas-bbq.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Pechuga a la Plancha',
                'description' => 'Pechuga de pollo a la plancha con hierbas, servida con arroz y vegetales',
                'price' => 16.00,
                'stock' => 35,
                'unit' => '1 Pieza',
                'image' => 'images/products/pechuga-plancha.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Nuggets de Pollo',
                'description' => 'Nuggets de pollo empanizados y fritos, servidos con salsa de tu elección',
                'price' => 12.00,
                'stock' => 45,
                'unit' => '8 Piezas',
                'image' => 'images/products/nuggets-pollo.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Hamburguesa de Pollo',
                'description' => 'Hamburguesa con pechuga de pollo, lechuga, tomate y aderezo especial',
                'price' => 14.00,
                'stock' => 25,
                'unit' => '1 Unidad',
                'image' => 'images/products/hamburguesa-pollo.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Ensalada César',
                'description' => 'Ensalada con pollo, lechuga romana, crutones y aderezo César',
                'price' => 13.00,
                'stock' => 20,
                'unit' => '1 Porción',
                'image' => 'images/products/ensalada-cesar.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Papas Fritas',
                'description' => 'Papas fritas crujientes con sal, servidas con ketchup',
                'price' => 8.00,
                'stock' => 60,
                'unit' => '1 Porción',
                'image' => 'images/products/papas-fritas.jpg',
                'category_id' => $comidasCategory->id,
                'status' => 'active'
            ]
        ];

        // Productos de bebidas
        $bebidas = [
            [
                'name' => 'Coca Cola',
                'description' => 'Refresco Coca Cola original, refrescante y delicioso',
                'price' => 3.50,
                'stock' => 100,
                'unit' => '500ml',
                'image' => 'images/products/coca-cola.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Sprite',
                'description' => 'Refresco Sprite limón lima, refrescante y burbujeante',
                'price' => 3.50,
                'stock' => 80,
                'unit' => '500ml',
                'image' => 'images/products/sprite.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Fanta',
                'description' => 'Refresco Fanta naranja, dulce y refrescante',
                'price' => 3.50,
                'stock' => 75,
                'unit' => '500ml',
                'image' => 'images/products/fanta.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Agua Mineral',
                'description' => 'Agua mineral natural, hidratante y saludable',
                'price' => 2.50,
                'stock' => 120,
                'unit' => '500ml',
                'image' => 'images/products/agua-mineral.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Jugo de Naranja',
                'description' => 'Jugo de naranja natural, rico en vitamina C',
                'price' => 4.00,
                'stock' => 40,
                'unit' => '300ml',
                'image' => 'images/products/jugo-naranja.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Limonada',
                'description' => 'Limonada natural con menta, refrescante y saludable',
                'price' => 4.00,
                'stock' => 35,
                'unit' => '300ml',
                'image' => 'images/products/limonada.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Chicha Morada',
                'description' => 'Bebida tradicional peruana hecha con maíz morado',
                'price' => 4.50,
                'stock' => 30,
                'unit' => '300ml',
                'image' => 'images/products/chicha-morada.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ],
            [
                'name' => 'Inca Kola',
                'description' => 'Refresco dorado peruano, sabor único y tradicional',
                'price' => 3.50,
                'stock' => 90,
                'unit' => '500ml',
                'image' => 'images/products/inca-kola.jpg',
                'category_id' => $bebidasCategory->id,
                'status' => 'active'
            ]
        ];

        // Crear productos de comidas
        foreach ($comidas as $comida) {
            Product::firstOrCreate(
                ['name' => $comida['name']],
                $comida
            );
        }

        // Crear productos de bebidas
        foreach ($bebidas as $bebida) {
            Product::firstOrCreate(
                ['name' => $bebida['name']],
                $bebida
            );
        }

        $this->command->info('Productos del menú de pollería creados exitosamente!');
    }
} 