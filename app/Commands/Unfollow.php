<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use DG\Twitter\Twitter;

class Unfollow extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'unfollow';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Unfollow all following';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $twitter = new Twitter(config('twitter.consumerKey'), $consumerSecret, $accessToken, $accessTokenSecret);
    }
}
