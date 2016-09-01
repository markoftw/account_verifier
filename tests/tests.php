<?php

include '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Markoftw\AccountChecker\Netflix as Netflix;
use Markoftw\AccountChecker\Spotify as Spotify;
use Markoftw\AccountChecker\Hulu as Hulu;
use Markoftw\AccountChecker\Minecraft as Minecraft;
use Markoftw\AccountChecker\Crunchyroll as Crunchyroll;

$netflix = new Netflix();

echo $netflix->proxy("127.0.0.1:5555")->check("user@email.com", "password")->json();

/*$spotify = new Spotify();
echo $spotify->proxy("127.0.0.1:5555")->check("user@email.com", "password")->json();*/

/*$hulu = new Hulu();
echo $hulu->check("user@email.com", "password")->json();*/

/*$minecraft = new Minecraft();
echo $minecraft->proxy("127.0.0.1:5555")->check("user@email.com", "password")->json();*/

/*$crunchyroll = new Crunchyroll;
echo $crunchyroll->proxy("127.0.0.1:5555")->check("user@email.com", "password")->json();*/
