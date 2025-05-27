<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;


class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $categories = [
            [
                'name' => 'Strategie di Studio',
                'slug' => 'strategie-studio',
                'description' => 'Condivisione di metodi e tecniche per ottimizzare lo studio',
                'color' => '#f59e0b',
                'order' => 1
            ],
            [
                'name' => 'Gestione del Tempo',
                'slug' => 'gestione-tempo',
                'description' => 'Tecniche per organizzare e pianificare le attività',
                'color' => '#3b82f6',
                'order' => 2
            ],
            [
                'name' => 'Risorse Didattiche',
                'slug' => 'risorse-didattiche',
                'description' => 'Materiali utili e risorse consigliate',
                'color' => '#10b981',
                'order' => 3
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
