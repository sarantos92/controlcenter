<?php

use Illuminate\Database\Seeder;
use App\Helpers\FactoryHelper;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Create the default dev accounts corresponding to VATSIM Connect
        for ($i = 1; $i <= 11; $i++) {

            $name_first = "Web";
            $name_last = "X";
            $email = "auth.dev".$i."@vatsim.net";

            $rating_id = 1;
            $group = null;

            switch($i){
                case 1:
                    $name_last = "One";                 
                    break;
                case 2:
                    $name_last = "Two";
                    $rating_id = 2;
                    break;
                case 3:
                    $name_last = "Three";
                    $rating_id = 3;
                    break;
                case 4:
                    $name_last = "Four";
                    $rating_id = 4;
                    break;
                case 5:
                    $name_last = "Five";
                    $rating_id = 5;
                    break;
                case 6:
                    $name_last = "Six";
                    $rating_id = 7;
                    break;
                case 7:
                    $name_last = "Seven";
                    $rating_id = 8;
                    $group = 3;
                    break;
                case 8:
                    $name_last = "Eight";
                    $rating_id = 10;
                    $group = 3;
                    break;
                case 9:
                    $name_last = "Nine";
                    $rating_id = 11;
                    $group = 2;
                    break;
                case 10:
                    $name_first = "Team";
                    $name_last = "Web";
                    $rating_id = 12;
                    $email = "noreply@vatsim.net";
                    $group = 1;
                    break;
                case 11:
                    $name_first = "Suspended";
                    $name_last = "User";
                    $rating_id = 0;
                    $email = "suspended@vatsim.net";
                    break;
            }

            factory(App\User::class)->create([
                'id' => 10000000 + $i,
                'group' => $group,
                'setting_notify_newreport' => false,
                'setting_notify_newreq' => false,
                'setting_notify_closedreq' => false,
                'setting_notify_newexamreport' => false,
            ]);
            factory(App\Handover::class)->create([
                'id' => 10000000 + $i,
                'email' => $email,
                'first_name' => $name_first,
                'last_name' => $name_last,
                'rating' => $rating_id,
                'rating_short' => FactoryHelper::shortRating($rating_id),
                'rating_long' => FactoryHelper::longRating($rating_id),
                'region' => "EMEA",
                'division' => "EUD",
                'subdivision' => "SCA",
            ]);
        }

        // Create random Scandinavian users
        for ($i = 12; $i <= 125; $i++) {
            factory(App\User::class)->create([
                'id' => 10000000 + $i,
            ]);
            factory(App\Handover::class)->create([
                'id' => 10000000 + $i,
                'region' => "EMEA",
                'division' => "EUD",
                'subdivision' => "SCA",
            ]);
        }

        // Create random users
        for ($i = 126; $i <= 250; $i++) {
            factory(App\User::class)->create([
                'id' => 10000000 + $i,
            ]);
            factory(App\Handover::class)->create([
                'id' => 10000000 + $i,
            ]);
        }

        // Populate trainings and other of the Scandinavian users
        for ($i = 1; $i <= rand(100, 125); $i++) {
            $training = factory(App\Training::class)->create();
            $training->ratings()->attach(App\Rating::where('vatsim_rating', '>', 1)->inRandomOrder()->first());

            // Give all non-queued trainings a mentor
            if($training->status > 0){
                $training->mentors()->attach(App\User::where('group', 3)->inRandomOrder()->first(), ['expire_at' => now()->addYears(5)]);
                factory(App\TrainingReport::class)->create([
                    'training_id' => $training->id,
                    'written_by_id' => $training->mentors()->inRandomOrder()->first(),
                ]);
            }

            // Give all exam awaiting trainings a solo endorsement
            if($training->status == 3){
                if (!App\SoloEndorsement::where('user_id', $training->user_id)->exists()) {
                    factory(App\SoloEndorsement::class)->create([
                        'user_id' => $training->user_id,
                        'training_id' => $training->id,
                    ]);
                }

                // And some a exam result
                if ($i % 7 == 0) {
                    factory(App\TrainingExamination::class)->create([
                        'training_id' => $training->id,
                        'examiner_id' => App\User::where('id', '!=', $training->user_id)->inRandomOrder()->first(),
                    ]);
                }
            }
            
        }
    }
}
