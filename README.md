# Public Link Telegram Files
Generate public links for your telegram files and auto remove. ([max size: 20MB](https://core.telegram.org/bots/api#getfile))
## Installation
Add your database information, and Telegram API token bot to `includes/settings.php`

```
/*  Database */
define('HOST','localhost'); # Database host name
define('DBNAME',''); # Database name
define('DBUSERNAME',''); # Database username
define('DBPASSWORD',''); # Database password

/*  Telegram Bot API Key */
define('API_KEY','');  # Enter bot api token
```

call the setWebHook method in the Bot API via the following url:
```
https://your_website.com/bot_path/index.php?setWebhook
```
or
```
https://api.telegram.org/bot{your_bot_token}/setWebhook?url={url_to_send_updates_to}
```

set new cron job for `cron.php`, for auto delete files.

## Settings
File `includes/settings.php` contains all the configurations.
you can change saved folder, files deleted after time, and change language.
