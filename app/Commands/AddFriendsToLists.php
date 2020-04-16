<?php

namespace App\Commands;

use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use DG\Twitter\Twitter;
use League\Csv\Reader;

/**
 * Class AddFriendsToLists
 * @package App\Commands
 */
class AddFriendsToLists extends Command
{
    /**
     * @var string
     */
    protected $signature = 'add-friends-to-lists {yourusername}';

    /**
     * @var string
     */
    protected $description = 'Add friends to lists';

    /** In-memory arrays */
    protected $listMembers = [];
    protected $existingLists = [];

    /**
     * @throws \DG\Twitter\Exception
     * @throws \League\Csv\Exception
     */
    public function handle()
    {
        $reader = Reader::createFromPath('friends_lists.csv', 'r');
        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();

        // First, create all lists
        $allLists = collect([]);
        foreach ($records as $offset => $record) {
            $list = Str::of($record['lists'])->explode(',')->map(function ($list) {
               return Str::of($list)->trim()->__toString();
            })->filter();

            $list->each(function ($list) use ($record) {
                if (!isset($this->listMembers[$list])) {
                    $this->listMembers[$list] = [];
                }
                $this->listMembers[$list][] = $record['screen_name'];
            });

            $allLists = $allLists->merge($list)->unique()->sort();
        }

        // Retrieve existing lists
        $twitterClient = new Twitter(config('twitter.consumerKey'), config('twitter.consumerSecret'), config('twitter.accessToken'), config('twitter.accessTokenSecret'));
        $existingListsResult = $twitterClient->loadUserLists($this->argument('yourusername'));

        // Create mapping with list name to list ID
        foreach ($existingListsResult as $list) {
            $this->existingLists[$list->name] = $list->id;
        }

        // Create non-existing lists
        $allLists->each(function ($listName) use ($twitterClient) {
            if (!isset($this->existingLists[$listName])) {
                try {
                    $newList = $twitterClient->createList($listName);
                    $this->existingLists[$listName] = $newList->id;
                    $this->info('Created list ' . $listName);
                } catch (\Exception $e) {
                    $this->error('Could not create list ' . $listName . ': ' . $e->getMessage());
                }
            }
        });

        // Add users to list
        foreach ($this->listMembers as $listName => $members) {
            try {
                $members = collect($members);
                $members->chunk(100)->each(function ($chunkedMembers) use ($twitterClient, $listName) {
                    $twitterClient->addMembersToList($this->existingLists[$listName], $chunkedMembers->toArray());
                    $this->info('Added the following members to list ' . $listName . ': ' . PHP_EOL . $chunkedMembers->implode(','));
                });
            } catch (\Exception $e) {
                $this->error('Could not added members to list ' . $listName . '.' . PHP_EOL . 'Error: ' . $e->getMessage() . PHP_EOL .'Members:' . PHP_EOL . implode(',', $members));
            }
        }

    }
}
