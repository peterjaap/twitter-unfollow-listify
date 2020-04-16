# Twitter Unfollow & Listify Toolkit

This is a small CLI application written to easily unfollow users, edit a CSV file to place them into lists, and import the list again.

**USE AT YOUR OWN RISK**

## Usage
Create a Twitter app at [developer.twitter.com](http://developer.twitter.com/) and place the credentials in `.env`;

```
TWITTER_CONSUMERKEY=''
TWITTER_CONSUMERSECRET=''
TWITTER_ACCESSTOKEN=''
TWITTER_ACCESSTOKENSECRET=''
```

First, run the unfollow command to unfollow *everyone* you are following:

```bash
$ ./twitter unfollow
```

This creates a CSV file with the unfollowed users' id, name, bio, and location. Based on this information you can place them into lists (comma separated in the `lists` column). You can leave the field blank to not place them in any list.

After you are done filling out the CSV, you can import the CSV; the application will create the list for you (if it doesn't exist) and add the users to that list.

## Notes

The functionality depends on a pull request being merged, which might not (yet) be the case; https://github.com/dg/twitter-php/pull/72

Twitter API Rate Limit may kick in when unfollowing, depending on how many users you follow. Be sure to backup the generated CSV file when an error occurs.

If you are subscribed to a list that isn't yours, you cannot add a user to that list. Use a different name or unsubscribe from that list first.
