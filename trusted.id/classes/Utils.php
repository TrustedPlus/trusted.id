<?php

namespace Trusted\Id;

class Utils
{

    /**
     * Debug info output
     * @param type $text Message
     * @param type $val Value
     * Value is printed using print_r
     */
    public static function debug($text, $val = null) {
        if (TR_ID_DEBUG) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);

            echo $caller['file'];
            echo ' ' . $caller['line'];
            echo PHP_EOL;
            if (!is_null($val)) {
                echo $text . ' ';
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
    public static function checkCurl() {
        return in_array('curl', get_loaded_extensions());
    }

    /**
     * Checks whether or not site runs on https.
     *
     * @return bool
     */
    public static function isSecure() {
        return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443);
    }

    /**
     * Random string with specified length and alphabet
     * @param number $len Length
     * @param string $alphabet Set of characters to generate from
     * @return string Password
     */
    public static function randomPassword($len = 8, $alphabet = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789') {
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $len; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public static function dump(...$vars) {
        $file = fopen($_SERVER["DOCUMENT_ROOT"] . "/log.txt", 'a');
        $time = date("H:i:s ");
        fwrite($file, $time . str_repeat("=", 30) . "\n");
        foreach ($vars as $var) {
            fwrite($file, print_r($var, true) . "\n\n");
        }
        fclose($file);
    }

    public static function dumpCallStack($full = false) {
        $stacktrace = debug_backtrace();
        unset($stacktrace[0]); // hide dumpCallStack call
        if ($full) {
            Utils::dump($stacktrace);
            return;
        }
        $res = '';
        foreach($stacktrace as $n => $node) {
            $res .= "$n. ".basename($node['file']) .": " .$node['function'] ." (" .$node['line'].")\n";
        }
        Utils::dump($res);
    }

}

