<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Project;
use App\Models\TicketStatus;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $projects = Project::all();
        $statuses = TicketStatus::all();

        foreach (range(1, 10) as $i) {
            Ticket::create([
                'name' => 'Sample Ticket ' . $i,
                'content' => 'This is the content for ticket ' . $i,
                'owner_id' => $users->random()->id,
                'type_id' => $statuses->random()->id,
                'responsible_id' => $users->random()->id,
                'status_id' => $statuses->random()->id,
                'project_id' => $projects->random()->id,
                'priority_id'=> $statuses->random()->id
                
                // Add other fields as needed
            ]);
        }
    }
}
