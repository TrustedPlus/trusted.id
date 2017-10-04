<?php

class TDataBase {

    /**
     * База данных
     * @var mysqli 
     */
    protected $db = null;
    protected $error = null;

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * Выводит текст последней ошибки
     * @return string
     */
    public function LastError() {
        return null;
    }

    /**
     * Открывает соединение с базой данных. Метод возвращает "true" при успешном открытии соединения или "false" при ошибке.
     * @return boolean
     */
    function Connect() {
        debug("TDataBase.connect: Connect to BITRIX data base");
        global $DB;
        $this->db = $DB;
        if ($DB) {
            return true;
        }
        return false;
    }

    /**
     * Закрывает соединение с базой данных.
     */
    function Disconnect() {
    }

    /**
     * Экранирует символы
     */
    function EscapeString($text) {
        return $text;
    }

    function LastID() {
        return $this->db->LastID();
    }

    /**
     * Метод выполняет запрос к базе данных и если не произошло ошибки возвращает результат
     * @param string $sql
     * @param boolean $ignore_errors
     * @param string $error_position
     * @param array $options
     */
    function Query($sql, $ignore_errors = false, $error_position = "", $options = array()) {
        debug("TDataBase.Query: SQL", $sql);
        return $this->db->Query($sql, $ignore_errors, $error_position, $options);
    }

}
