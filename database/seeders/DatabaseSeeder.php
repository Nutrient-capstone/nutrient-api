<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $users = User::get();
        foreach ($users as $user) {
            $gender = $user->userData->gender;
            $age = Carbon::parse($user->userData->birthdate)->age;
            $weight = $user->userData->weight;
            $height = $user->userData->height;
            $resultBmr = $this->countBmr($gender, $age, $weight, $height);
            $user->dailyIntake()->create([
                "max_calories" => $resultBmr,
                "user_id" => $user->id
            ]);
        }
    }

    protected function countBmr(Int $gender, Int $age, Float $weight,  Float $height)
    {

        if ($gender) { // jika gender adalah lanang
            $bmr = (66.5 + (13.75 * $weight) + (5.003 * $height) - (6.75 * $age)) * 1.3;
        } else { // jika gender adalah wedok
            $bmr = (655.1 + (9.563 * $weight) + (1.850 * $height) - (4.676 * $age)) * 1.3;
        }

        return $bmr;
    }
}
