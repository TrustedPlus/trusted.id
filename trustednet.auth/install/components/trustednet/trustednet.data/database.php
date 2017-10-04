<?php

class TDataBase {

    /**
     * ���� ������
     * @var mysqli 
     */
    protected $db = null;
    protected $error = null;

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * ������� ����� ��������� ������
     * @return string
     */
    public function LastError() {
        return null;
    }

    /**
     * ��������� ���������� � ����� ������. ����� ���������� "true" ��� �������� �������� ���������� ��� "false" ��� ������.
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
     * ��������� ���������� � ����� ������.
     */
    function Disconnect() {
    }

    /**
     * ���������� �������
     */
    function EscapeString($text) {
        return $text;
    }

    function LastID() {
        return $this->db->LastID();
    }

    /**
     * ����� ��������� ������ � ���� ������ � ���� �� ��������� ������ ���������� ���������
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
