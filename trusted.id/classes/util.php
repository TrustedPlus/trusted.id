<?php

/**
 * Debug info output
 * @param type $text Message
 * @param type $val Value
 * Value is printed using print_r
 */
function debug($text, $val = null) {
    if (TR_ID_DEBUG) {
        $bt = debug_backtrace();
        $caller = array_shift($bt);

        echo $caller['file'];
        echo " " . $caller['line'];
        echo PHP_EOL;
        if (!is_null($val)) {
            echo $text . " ";
        } else {
            $val = $text;
        }
        print_r($val);
        echo PHP_EOL;
    }
}

/**
 * Check if curl extension is available.
 * @return bool
 */
function checkCurl() {
    return in_array('curl', get_loaded_extensions());
}

/**
 * Checks whether or not site runs on https.
 *
 * @return bool
 */
function isSecure() {
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443);
}

/**
 * Random string with specified length and alphabet
 * @param number $len Length
 * @param string $alphabet Set of characters to generate from
 * @return string Password
 */
function randomPassword($len = 8, $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789") {
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $len; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

class CDirectory {

    protected $path;

    public function getPath() {
        return $this->path;
    }

    public static function getFileName($path) {
        $dirs = explode("/", $path);
        $len = count($dirs);
        return $dirs[--$len];
    }

    public static function getLocalRoot() {
        return TR_ID_PROJECT_ROOT;
    }

    public static function getHttpRoot() {
        return TRUSTED_PROJECT_HOST;
    }

    public function getSystemPath() {
        return CDirectory::getLocalRoot() . '/' . $this->path;
    }

    public function getHttpPath() {
        return CDirectory::getHttpRoot() . '/' . $this->path;
    }

    public static function exists($path) {
        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    public function remove($cb = null) {
        unlink($this->path);
        if (isset($cb)) {
            $cb();
        }
    }

    public static function create($path, $cb = null) {
        $dirs = explode("/", $path);


        if (!CDirectory::exists($path)) {
            mkdir($path, 0777, true);
            $created = true;
        }

        $res = CDirectory::open($path);
        if ($created && isset($cb)) {
            $cb($res);
        }
        return $res;
    }

    public static function open($path) {
        $res = null;
        if (CDirectory::exists(CDirectory::getLocalRoot() . '/' . $path)) {
            $res = new CDirectory();
            $res->path = $path;
        }
        return $res;
    }

}

