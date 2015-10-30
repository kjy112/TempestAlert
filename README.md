# TempestAlert

TempestAlert is a program that alerts you VIA email and/or text message when a tempest with a set of preffix and/or suffix are reported on http://poetempest.com. This program is made to enhance the game play for Path Of Exile. 

Documentation utilizes DocBlock practice, and the documentation is generated with phpDocumentor. Composer is used for managing dependecy. Requires PHP 5.4+

## Usage
Make sure you set the following setting in include/config.inc.php with your own information

```
define('EMAIL_NOTIFICATION', TRUE);
define('SMS_NOTIFICATION', TRUE);
define('CONTACT_EMAIL', 'me@yourname.name');
define('CONTACT_SMS', '5555555555');
//TMobile. Please refer to https://en.wikipedia.org/wiki/SMS_gateway
define('CONTACT_SMS_PROVIDER', 'tmomail.net'); 
```

Then setup a Cron job that runs every 5 mins, or however often you want to check the tempest, on poetempest.php:

```
php /<your server path to the script>/poetempest.php
```

## Links
https://github.com/jayvan/tempest-watch - Github for tempest-watch aka http://poetempest.com<br/>
https://getcomposer.org/ - Composer is a dependency manager for PHP<br/>
http://www.phpdoc.org/ - phpDocumentor<br/>
https://www.pathofexile.com/ - Path of Exile is an online Action RPG set in the dark fantasy world of Wraeclast.<br/>
http://www.grindinggear.com/ - Grinding Gear Games develops Path of Exiles
