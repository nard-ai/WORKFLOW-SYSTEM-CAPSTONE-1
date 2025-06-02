<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SignatureStyle;

class SignatureStyleSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            [
                'name' => 'Elegant Script',
                'font_family' => 'Mr Dafoe',
            ],
            [
                'name' => 'Handwritten',
                'font_family' => 'Homemade Apple',
            ],
            [
                'name' => 'Flowing Script',
                'font_family' => 'Pacifico',
            ],
            [
                'name' => 'Classic Script',
                'font_family' => 'Dancing Script',
            ],
        ];

        foreach ($styles as $style) {
            SignatureStyle::create($style);
        }
    }
} 