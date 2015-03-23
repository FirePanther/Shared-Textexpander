# Shared-Textexpander
Share your Textexpander files and download shared files (update them automatically with a cron job)

## Configuration
Open `update.php` with an editor of your choice.

### Identification / Authorization
Add your email address, pick a username and a password. We won't send you any mails, it's just for recovering your password, should you forget it.
If you're using this script the first time and don't have a user, pick a name, it will create the user with the first request (read the comments in the script for more informations).

### Groups
You can either use an array for your groups, like this:
```php
$groups = ["Shared group", "Testgroup", "HTML5", "PHP Snippets"];
```

or a comma separated string (the strings will be trimmed):
```php
$groups = "Shared group, Testgroup,HTML5, PHP Snippets";
```

### Textexpander file
In the Textexpander preferences, open "Sync" and choose "Dropbox" as synchronization method.

In the "update.php" file write the path to the ".textexpander" file.
You can use a `~` (tilde) as your $HOME directory.

### temp directory
The updater creates a cache file. The temp directory is the location, where the file will be created.

## Cronjob
Create a cronjob to run `update.php` like this:
```bash
php -f ~/Documents/update.php
```

If you don't know how to create a cron job, you can use [Cronnix for OS X](https://code.google.com/p/cronnix/).
If you edit your snippets very frequently, create a cronjob which will be run daily or more often, otherwise create a cronjob scheduled weekly.

## Windows
Windows users have to install a webserver like [XAMPP](https://www.apachefriends.org/de/index.html)

# Contact / Support
If you have any suggestions or problems, just contact me: textexpander [at] suat [dot] be