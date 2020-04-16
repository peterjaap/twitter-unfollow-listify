<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use DG\Twitter\Twitter;
use League\Csv\Writer;

/**
 * Class Unfollow
 * @package App\Commands
 */
class Unfollow extends Command
{
    /**
     * @var string
     */
    protected $signature = 'unfollow';

    /**
     * @var string
     */
    protected $description = 'Unfollow all following';


    /**
     * @throws \DG\Twitter\Exception
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle()
    {
        $twitterClient = new Twitter(config('twitter.consumerKey'), config('twitter.consumerSecret'), config('twitter.accessToken'), config('twitter.accessTokenSecret'));

        $unfollowed = [];
        $followers = $twitterClient->loadUserFriendsList('peterjaap');
        foreach ($followers->users as $follower) {
            try {
                if ($this->confirm('Do you want to unfollow ' . $follower->screen_name . '?', true)) {
                    $twitterClient->unfriend(null, $follower->id);
                    $this->info('Unfollowed ' . $follower->screen_name);
                    $unfollowed[] = [$follower->id, $follower->screen_name, $follower->bio, ''];
                }
            } catch (\Exception $e) {
                $this->error('Could not unfollow: ' . $e->getMessage());
            }
        }

        $writer = Writer::createFromPath('friends_lists.csv', 'w+');
        $writer->insertOne(['id','name','bio','location','lists']);
        $writer->insertAll($unfollowed);

        $this->info('Wrote friends info to friends_lists.csv file. Fill out the Lists column (comma separated) and run add-friends-to-list command');
    }
}
