<?php

include '../../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('content-type: application/json; charset=utf-8');

use Markoftw\AccountChecker\Netflix as Netflix;
use Markoftw\AccountChecker\Spotify as Spotify;
use Markoftw\AccountChecker\Minecraft as Minecraft;

if (isset($_GET['service'], $_GET['email'], $_GET['password']) && !empty($_GET['service']) && !empty($_GET['email']) && !empty($_GET['password'])) {

    $email = trim($_GET['email']);
    $password = trim($_GET['password']);

    if ($_GET['service'] == "netflix") {

        $netflix = new Netflix();
        //echo $netflix->proxy("127.0.0.1:5555")->check($email, $password)->json();
        echo $netflix->check($email, $password)->json();

    } elseif ($_GET['service'] == "spotify") {

        $spotify = new Spotify();
        echo $spotify->proxy("127.0.0.1:5555")->check($email, $password)->plan()->json();

    } elseif ($_GET['service'] == "hulu") {

    } elseif ($_GET['service'] == "minecraft") {

        $minecraft = new Minecraft();
        echo $minecraft->proxy("127.0.0.1:5555")->check($email, $password)->json();

    } elseif ($_GET['service'] == "directtv") {

    } elseif ($_GET['service'] == "crunchyroll") {

    }

} elseif (isset($_GET['proxy'])) {

    if (isset($_GET['ip'], $_GET['port']) && !empty($_GET['ip']) && !empty($_GET['port'])) {

    }

} elseif (isset($_GET['proxy-test'])) {

}
