*For educational purposes only - some components might be outdated*

# Account checker

**Multi website account checker**

*AccountChecker* is a simple tool to check username and email combos for different providers written in PHP.

## Features

* Account checker for the following websites:
    + Netflix (finished)
    + Hulu (in progress - mink)
    + Spotify (finished)
    + HBO, HBOGO, HBONOW (planned)
    + Minecraft (finished - todo: purchased game?)
    + Crunchyroll (in progress - mink)
    + DirectTV (planned)
    + PSN (planned)
    + XBox (planned)

## Requirements

* PHP 5.5+
    + ext/curl
    + ext/json

## Installation

Installation by composer:

    composer require markoftw/account_verifier

## Include vendor

Include vendor in your PHP file:
```
    include '../vendor/autoload.php';
```

### Usage examples

The following example will return an array of the results:

    use Markoftw\AccountChecker\Netflix as Netflix;
    
    $netflix = new Netflix();
    print_r($netflix->check("email", "password")->get());
    
!IMPORTANT! Proxy must be specified before check() is called.
Other possible usages are (proxy): 

    $netflix->proxy("127.0.0.1:5555")->check("email", "password")->get();

Default proxy type is SOCKS5. If you want to use other proxies specify second argument: 

    $netflix->proxy("127.0.0.1:5555", "HTTP")->check("email", "password")->get();

plan details:

    $netflix->proxy("127.0.0.1:5555")->check("email", "password")->plan()->get();

save to file, default filename is results_*SERVICE*.txt, you can change it by passing an argument to save:

    $netflix->proxy("127.0.0.1:5555")->check("email", "password")->save()->get();

json output:

    echo $netflix->proxy("127.0.0.1:5555")->check("email", "password")->plan()->save()->json();
    