<?php

namespace Bozboz\Jam\Seeds;

use Illuminate\Database\Seeder;

class JamSeeder extends Seeder
{
    public function run()
    {
        array_map([$this, 'runSeeder'], glob(database_path('seeds/*JamTemplate.php')));
    }

    private function runSeeder($class) {
        $this->call(str_replace('.php', '', basename($class)));
    }
}
