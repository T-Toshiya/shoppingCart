<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB:: table('products')->insert([
            'productName' => str_random(10),
            'productPrice' => rand(100, 1000),
            'imagePath' => 'book10.jpeg'
        ]);
    }
}
