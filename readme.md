# FreeAdvertising
Done initially with Test Driven Development on both React-Router and Laravel PHP with SASS, this is a simple webapplication for hosting an user submitted banner/advertisement platform with no direct monetization.<br/>
Uses Nginx, MySQL, Larvel and React ~and Docker(laradock)~. Docker is a consideration. <br/>Testing done with PHPUnit, Jest+Enzyme. 
<br/>

## About
This application cycles through a set of banners randomly. If someone wishes they can put the banner container into their website using either a raw json method or an iframe-page method. There is support for moderation and shadow banning, but these super users must be assigned through mysql into the mods table. In order to give contributors an idea of what else is on the site /all lists all banners submitted.  

## Configuration

If you want to host your own version you must:
1) Consider a dontation: https://www.patreon.com/ECHibiki
2) Create your .env file from .env.example. Currently this will require npm configuration and rebuilds.
3) Adjust app/http/controllers/ConfidentialInfoController.php to have the image dimensions desired. In settings.js change where desired.
4) Setup is typically concluded by ```composer install --no-scripts```. This requires an install of various laravel php dependencies <a href="https://laravel.com/docs/5.8/installation">https://laravel.com/docs/5.8/installation</a>. If it fails chances are you don't have enough memory. You should manually add them from a desktop computer/VM into the server in this case.
5) ```php artisan key:generate```, ```php artisan link:storage```, ```php artisan migrate```
6) ```php artisan serve```

#Requirements

FA runs on the simplest of systems. You just need: 
- PHP >= 7.2.5
- MySQL 
- php-mysql
- NPM

### Considerations

<a href="https://www.patreon.com/ECHibiki"><img src="https://banners.kissu.moe" />
</a>

