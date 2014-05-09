Twitter Connect
===============

A Basic & Light Twitter Connector for Laravel 4.1

Please contribute!

I know there are a lot of packages for this, or library to do it, just i feel it is too much to include, to much process for the goal i had.
Just simple as that, Connect, Authorize, Get User Data, my application does not need to push to tw, in the near future, i'll add more things!


Installation
------------

Using Composer [composer](https://getcomposer.org/download/).

You need to add the repo for the project first in your composer.json:

```
	"repositories": [
        {
            "type":"vcs",
            "url": "https://github.com/mikkezavala/twitter.git"
        }
    ]
```

In the same require the package:

```
	{
	    "require": {
			"mikke/twitter": ">=0.0.4",
	    }
	}
```

Then just run a composer update

```
	$ composer update	

```

You can add the alias in your app/config/app.php

```php

		'Twitter'  		  => 'Mikke\Twitter\Twitter',
```


Usage
----------

It can be called in your controller wherever you want, just this a quick example, conecting and getting back basic information

You can create a config file, use existent one, or whatever you want to manage the keys, in my case i created a file called app/config/api.php
and it looks like:

File: app/config/api.php
```php

	return array(
		'twitter' => array(
			'api_key' => 'XXXXXXXXXXXXXXX',
			'api_secret' => 'XXXXXXXXXXXXXXXXXXXX',
			'api_callback' => 'http://some.com/callback',
			'api_token' => '',
			'api_token_secret' => '',
		)
	);


````


File: app/routes.php

```php

	Route::get('/', function(){
		$twitter = new TwitterConnect(Config::get('api.twitter'));
		$twitter->authorize();
	});
	Route::get('twitter/validate', function(){
		
		Config::set('api.twitter.api_token', Input::get('oauth_token'));
		$twitter = new TwitterConnect(Config::get('api.twitter'));
		
		if($twitter->access_token(Input::get('oauth_verifier'))){
			$data = $twitter->user_data();
			print_r($data);
		}
		
		
	});
```

Contribute
----------

This is just a simple connector, you can fork me, or contribute to this, the idea is this to make it easier, and collaborative, in the next week i'll be releasing more connectors
		
