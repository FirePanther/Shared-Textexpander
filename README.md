# Shared-Textexpander
Share your Textexpander files and download shared files (update them automatically with a cron job)

## Configuration
Open the file "update.php" with an editor.

### Identification / Authorization
Add your email address, pick an username and a password. We wont send any mails, it's just for recovering your password, if you should forget it.
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
Create a cron job which runs the "update.php", like this:
```
php -f ~/Documents/update.php
```

If you don't know how to create a cron job, you can use [Cronnix for OS X](https://code.google.com/p/cronnix/).
If you edit your snippets very frequently, create a cronjob which will be run daily or more often, otherwise create a cronjob scheduled weekly.

# Contact / Support
If you have any suggestions or problems, just contact me: textexpander [at] suat [dot] be