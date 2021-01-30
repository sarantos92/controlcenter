<?php

namespace App\Console\Commands;

use App\Handover;
use App\Rating;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UpdateAtcActiveStatus extends Command
{

    private $base_api_url = 'https://api.vatsim.net/api/ratings/';
    private $count_updated = 0;
    private $count_visited = 0;
    private $dry_run = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:atcactive {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the ATC active status of members which holds an ATC rating';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $start_time = microtime(true) * 1000;

        if ($this->option('dry-run') != null) {
            $this->dry_run = true;
        }

        $users = $this->getUsers();

        if (sizeof($users) == 0)
            return;

        $client = new \GuzzleHttp\Client();

        foreach ($users as $user) {

            $this->info("Checking {$user->id}");

            $url = $this->getQueryString($user->id);
            $response = $this->makeHttpGetRequest($client, $url);

            if ($response == null || $response->getStatusCode() >= 300)
                continue;

            $parsed_data = null;
            $this->parseJsonResponse($parsed_data, $response);

            if ($parsed_data == null) {
                continue;
            }

            if ($parsed_data['count'] == 0 || empty($parsed_data['results'])) {
                // User has not had any sessions - set as inactive.
                $this->setAsInactive($user);
                continue;
            }

            $sessions = collect($parsed_data['results']);

            $this->addNextPagesToResult($sessions, $parsed_data, $client);

            $sum = $sessions->sum('minutes_on_callsign');

            $this->count_visited++;

            if ($this->userShouldBeSetAsInactive($user, $sum)) {
                // User should be set as inactive
                $this->setAsInactive($user);
                continue;
            }

            $this->setAsActive($user);

        }

        $end_time = microtime(true) * 1000;

        if ($this->dry_run) {
            $this->info('Would have updated a total of ' . $this->count_updated . ' users. A total of ' . $this->count_visited . ' users were checked.');
        } else {
            $this->info('Updated a total of ' . $this->count_updated . ' users. A total of ' . $this->count_visited . ' users were checked.');
        }

        $this->info('Command took ' . ($end_time - $start_time) / 1000 . ' seconds to process');
    }

    /**
     * Make HTTP GET request
     *
     * @param \GuzzleHttp\Client $client
     * @param string $url
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    private function makeHttpGetRequest(\GuzzleHttp\Client $client, string $url)
    {
        try {
            $response = $client->get($url);
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            // As this is going to be run on the cron, we don't want it to fail on simpler exceptions
        }

        if (isset($response))
            return $response;

        return null;
    }

    /**
     * Add results from next pages to the results variable provided.
     *
     * @param Collection $results
     * @param $response
     * @param \GuzzleHttp\Client $client
     */
    private function addNextPagesToResult(Collection &$results, $response, \GuzzleHttp\Client $client)
    {
        if ($response['next'] == null || strcasecmp($response['next'], '') == 0)
            return;

        $next_response = $this->makeHttpGetRequest($client, $response['next']);

        if ($next_response == null || $next_response->getStatusCode() >= 300)
            return;

        $parsed_data = null;
        $this->parseJsonResponse($parsed_data, $next_response);

        if ($parsed_data == null)
            return;

        $results->add($parsed_data['results']);

        if ($parsed_data['next'] != null && strcasecmp($parsed_data['next'], '') == 0)
            $this->addNextPagesToResult($results, $next_response, $client);
    }

    /**
     * Parse the response
     *
     * @param $data
     * @param $response
     */
    private function parseJsonResponse(&$data, $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
    }

    // Get functions

    /**
     * Get users that should be checked
     *
     * @return mixed
     */
    private function getUsers()
    {
        // Rating >= 2 means S1+
        // Subdivision only SCA
        return Handover::where([
            ['rating', '>=', 2],
            ['subdivision', '=', 'SCA']
        ])->get();
    }

    /**
     * Get the query string for the http call
     *
     * @param int $user_id
     * @return string
     */
    private function getQueryString(int $user_id): string
    {
        $query_string = $this->base_api_url . $user_id;
        $query_string .= '/atcsessions/';
        $query_string .= '?start=' . Carbon::now()->subMonths(12)->format('Y-m-d');
        return $query_string;
    }

    // Set functions

    /**
     * Set specified user as inactive
     *
     * @param Handover $user
     */
    private function setAsInactive(Handover $user)
    {
        $this->setAtcActiveStatus($user, false);
    }

    /**
     * Set specified user as active
     *
     * @param Handover $user
     */
    private function setAsActive(Handover $user)
    {
        $this->setAtcActiveStatus($user, true);
    }

    /**
     * Set user atc_active status according to param
     *
     * @param Handover $user
     * @param bool $is_active
     */
    private function setAtcActiveStatus(Handover $user, bool $is_active)
    {
        $user = $user->fresh();
        if ($user->atc_active == $is_active)
            return;

        $this->count_updated++;

        if ($this->dry_run)
            return;

        // We need to "manually" set the value to trigger any event subscribers...
        $user->atc_active = $is_active;
        $user->save();
    }

    /**
     * Get trainings from a collection of trainings that should be
     * used for counting the grace period.
     *
     * @param $trainings
     */
    private function getGracePeriodTrainings(Collection &$trainings)
    {
        foreach ($trainings as $key => $training) {
            if ($training->ratings->contains(Rating::where('vatsim_rating', 3)->get()->first()->id) || in_array($training->type, [2, 3, 4])) {
				// Training is an S2 training or refresh, fast-track or familiarisation.
				continue;
            }
            $trainings->pull($key);
        }
    }

    /**
     * Determine if the user should be set as inactive or not.
     * This method will take in to account any recently
     * completed trainings.<br>
     * This is to allow for newer controllers to not get
     * penalized for not having reached the required hours.
     *
     * @param Handover $handover
     * @param $sum
     * @return bool
     */
    private function userShouldBeSetAsInactive(Handover $handover, $sum)
    {
        // TODO: Move hardcoded 10 hrs to value that can be changed
        if (round(($sum / 60)) >= 10)
            return false;

        $user = $handover->user;

        if ($user == null) {
            // User does not exist in CC. Set as inactive.
            return true;
        }

        $trainings = $user->trainings;

        // User has had no trainings
        if (count($trainings) <= 0)
            return true;

        // Get completed trainings
        $completed_trainings = $trainings->where('status', -1);

        $completed_tmp_tranings = $trainings->where('status', -1)->whereIn('type', [2, 3, 4]);

        $completed_trainings = collect(array_merge($completed_trainings, $completed_tmp_tranings))->sortBy('closed_at');

        // Remove all non-S2 trainings
        $this->getGracePeriodTrainings($completed_trainings);

        if ($completed_trainings->last() != null && $completed_trainings->last()->closed_at->diffInMonths(now()) < 12) {
            // User had trainings qualified for grace period and training was completed within last 12 months.
            // Do not set as inactive.
            return false;
        }

        return true;
    }

}
