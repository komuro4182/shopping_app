<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [[
            'name' => '食品',
        ],
        [
            'name' => '日用品',
        ],
        [
            'name' => '娯楽',
        ],
        [
            'name' => '交通費',
        ],
        [
            'name' => '教養',
        ],
        [
            'name' => 'その他',
        ],];
        DB::table('categories')->insert($param);
    }
}
