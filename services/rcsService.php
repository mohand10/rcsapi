<?php
interface RcsApiService {


    public static function getPhoneByPhoneID($id);
    public static function getAllPhones();
    public static function getPhonesByServerID($serverId);
    public static function getAccountSummary($phoneId);
    public static function deleteByPhoneId($data);
    public static function addPhone($data);
    public static function updatePhonesByPhoneId($pid, $data);
}


?>