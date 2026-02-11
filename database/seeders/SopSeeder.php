<?php

namespace Database\Seeders;

use App\Models\SopChecklist;
use Illuminate\Database\Seeder;

class SopSeeder extends Seeder
{
    public function run()
    {
        $morning = SopChecklist::create([
            'name' => 'Morning Opening',
            'shift' => 'morning',
            'deadline_time' => '10:00:00'
        ]);

        $morning->items()->createMany([
            ['task' => 'Check Fridge Temperatures', 'sort_order' => 1],
            ['task' => 'Sanitize Surfaces', 'sort_order' => 2],
            ['task' => 'Turn on Ventilation', 'sort_order' => 3],
        ]);

        $closing = SopChecklist::create([
            'name' => 'Kitchen Closing',
            'shift' => 'closing',
            'deadline_time' => '23:00:00'
        ]);

        $closing->items()->createMany([
            ['task' => 'Empty Trash Bins', 'sort_order' => 1],
            ['task' => 'Turn off Gas Valves', 'sort_order' => 2],
            ['task' => 'Mop Floors', 'sort_order' => 3],
        ]);
    }
}
