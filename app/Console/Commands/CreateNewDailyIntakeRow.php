<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\DailyIntake;
use Illuminate\Console\Command;
use App\Http\Controllers\UserController;

class CreateNewDailyIntakeRow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-new-daily-intake-row';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
