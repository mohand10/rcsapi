<?php
include_once __DIR__ . './../../include/MySQL.php';
require __DIR__ . './../services/rcsService.php';
require __DIR__ . './../utils/util.php';
require __DIR__ . './../include/Command.php';
require __DIR__ . './../include/AddCommand.php';
require __DIR__ . './../include/DeleteCommand.php';
MySQL::connect();
class RcsModel implements RcsApiService
{

    public static function getPhoneByPhoneID($id)
    {
        //rcs phone query
        $sql = "SELECT LPAD(HEX(PhoneID), 12, '000000000000') AS PID, Server.Name AS ServerName, " .
            "Branding.Name AS BrandingName, Vendor.Name as VendorName, Firmware.Version AS FirmwareName, " .
            "TimesRegistered, IPAddress, DateRegistered, LastRegistered, Phone.DateAdded,Phone.VendorID /*, Phone.referenceID*/ " .
            "FROM Phone " .
            "LEFT JOIN Server ON (Server.ServerID=Phone.ServerID) " .
            "LEFT JOIN Branding ON (Branding.BrandingID=Phone.BrandingID) " .
            "LEFT JOIN Vendor ON (Vendor.VendorID=Phone.VendorID) " .
            "LEFT JOIN Firmware ON (Firmware.FirmwareID=Phone.FirmwareID) ";
        $sql .= "WHERE Phone.PhoneID=$id";
        $phonequery = MySQL::runQuery($sql);
        $arrRow = array();
        while ($row = MySQL::getNextRow()) {
            //$tmp = preg_replace('/(..)(..)(..)(..)(..)(..)/', '$1-$2-$3-$4-$5-$6', $row["PID"]);
            $arrRow['ServerName']       = iconv("ISO-8859-1", "UTF-8", $row["ServerName"]);
            $arrRow['BrandingName']     = iconv("ISO-8859-1", "UTF-8", $row["BrandingName"]);
            $arrRow['VendorName']       = iconv("ISO-8859-1", "UTF-8", $row["VendorName"]);
            $arrRow['FirmwareName']     = $row["FirmwareName"];
            $arrRow['TimesRegistered']  = $row["TimesRegistered"];
            $arrRow['IPAddress']        = $row["IPAddress"];
            $arrRow['DateRegistered']   = $row["DateRegistered"];
            $arrRow['LastRegistered']   = $row["LastRegistered"];
        }
        if (MySQL::getAffectedRows() >= 1) {
            $resArr["status code"]   = 200;
            $resArr["phone details"] = $arrRow;
            return json_encode($resArr);
        } else {
            throw new Exception("No phone details found - check with the PhoneID", 404);
        }
    }

    public static function getAllPhones()
    {
        $sql = "SELECT LPAD(HEX(PhoneID), 12, '000000000000') AS PID, Server.Name AS ServerName, " .
            "Branding.Name AS BrandingName, Vendor.Name as VendorName, Firmware.Version AS FirmwareName, " .
            "TimesRegistered, IPAddress, DateRegistered, LastRegistered, Phone.DateAdded,Phone.VendorID /*, Phone.referenceID*/ " .
            "FROM Phone " .
            "LEFT JOIN Server ON (Server.ServerID=Phone.ServerID) " .
            "LEFT JOIN Branding ON (Branding.BrandingID=Phone.BrandingID) " .
            "LEFT JOIN Vendor ON (Vendor.VendorID=Phone.VendorID) " .
            "LEFT JOIN Firmware ON (Firmware.FirmwareID=Phone.FirmwareID) " .
            "limit 5";

        $phonequery = MySQL::runQuery($sql);
        $arrRow = array();
        while ($row = MySQL::getNextRow()) {
            $arrRow[] = array(
                "ServerName" => iconv("ISO-8859-1", "UTF-8", $row["ServerName"]),
                "BrandingName" => iconv("ISO-8859-1", "UTF-8", $row["BrandingName"]),
                "VendorName" => iconv("ISO-8859-1", "UTF-8", $row["VendorName"]),
                "FirmwareName" => $row["FirmwareName"],
                "TimesRegistered" => $row["TimesRegistered"],
                "IPAddress" => $row["IPAddress"],
                "DateRegistered" => $row["DateRegistered"],
                "LastRegistered" => $row["LastRegistered"],
            );
        }
        if (MySQL::getAffectedRows() >= 1) {
            $resArr["status code"] = 200;
            $resArr["phone details"] = $arrRow;
            return json_encode($resArr);
        } else {
            throw new Exception("No phone(s) details found", 404);
        }
    }

    public static function getPhonesByServerID($serverId)
    {
        $sql = "SELECT LPAD(HEX(PhoneID), 12, '000000000000') AS PID, Server.Name AS ServerName, " .
            "Branding.Name AS BrandingName, Vendor.Name as VendorName, Firmware.Version AS FirmwareName, " .
            "TimesRegistered, IPAddress, DateRegistered, LastRegistered, Phone.DateAdded,Phone.VendorID /*, Phone.referenceID*/ " .
            "FROM Phone " .
            "LEFT JOIN Server ON (Server.ServerID=Phone.ServerID) " .
            "LEFT JOIN Branding ON (Branding.BrandingID=Phone.BrandingID) " .
            "LEFT JOIN Vendor ON (Vendor.VendorID=Phone.VendorID) " .
            "LEFT JOIN Firmware ON (Firmware.FirmwareID=Phone.FirmwareID) " .
            "WHERE Phone.serverID=$serverId limit 5";

        $phonequery = MySQL::runQuery($sql);
        $arrRow = array();
        while ($row = MySQL::getNextRow()) {
            $arrRow[] = array(
                "ServerName" => iconv("ISO-8859-1", "UTF-8", $row["ServerName"]),
                "BrandingName" => iconv("ISO-8859-1", "UTF-8", $row["BrandingName"]),
                "VendorName" => iconv("ISO-8859-1", "UTF-8", $row["VendorName"]),
                "FirmwareName" => $row["FirmwareName"],
                "TimesRegistered" => $row["TimesRegistered"],
                "IPAddress" => $row["IPAddress"],
                "DateRegistered" => $row["DateRegistered"],
                "LastRegistered" => $row["LastRegistered"],
            );
        }
        if (MySQL::getAffectedRows() >= 1) {
            $resArr["status code"] = 200;
            $resArr["phone details"] = $arrRow;
            return json_encode($resArr);
        } else {
            throw new Exception("No phone details found - check with the ServerID", 404);
        }
    }
    //account summary
    public static function getAccountSummary($vid)
    {
        $tables = array("Phone", "Server", "User", "Branding");
        $counts = array();
        $counts["Unaffiliated"] = "";
        $counts["Unregistered"] = "";
        $counts["Branding"] = "";
        $counts["MaxBrandings"] = "";
        $counts["Server"] = "";
        $counts["Phone"] = "";

        $q = "";
        foreach ($tables as $t) {
            $q .= "(SELECT \"$t\" AS Label, COUNT(${t}ID) AS Count FROM $t WHERE VendorID=$vid GROUP BY VendorID) UNION ALL ";
        }

        // Add Unaffiliated Phones.
        $tables[] = "Unaffiliated";
        $q .= "(SELECT \"Unaffiliated\" AS Label, COUNT(PhoneID) AS Count FROM Phone WHERE VendorID=$vid AND ServerID IS NULL GROUP BY VendorID) UNION ALL ";

        // Get Maximum number of Brandings;
        $tables[] = "MaxBrandings";
        $q .= "(SELECT \"MaxBrandings\" AS Label, NumBrandings FROM Vendor WHERE VendorID=$vid) UNION ALL ";

        $tables[] = "Unregistered";
        $q .= "(SELECT \"Unregistered\" AS Label, Count(PhoneID) AS Count FROM Phone WHERE (TimesRegistered IS NULL OR TimesRegistered = 0) AND VendorID=$vid GROUP BY VendorID)";

        MySQL::runQuery($q);
        if (MySQL::getAffectedRows() >= 1) {
            $tlab = "";
            $tval = "";
            while (($row = MySQL::getNextRow()) != NULL) {
                $tlab = $row["Label"];
                $tval = $row["Count"];
                $counts[$row["Label"]] = $row["Count"];
                // error_log("\n Row 3 Data $tlab = $tval \n",3,"/var/tmp/my-errors.log");            
            }
        } else {
            foreach ($tables as $t) {
                array_push($counts, "0");
            }
        }

        $q = "SELECT SQL_CALC_FOUND_ROWS Vendor.VendorID, Branding.Name, Firmware.Version FROM Vendor " .
            "LEFT JOIN (Branding) ON (Vendor.DefaultBranding = Branding.BrandingID) " .
            "LEFT JOIN (Firmware) ON (Vendor.DefaultFirmware = Firmware.FirmwareID) " .
            "WHERE Vendor.VendorID = $vid";

        MySQL::runQuery($q);
        if (MySQL::getAffectedRows() >= 1) {
            $row = MySQL::getNextRow();
            $defaultbranding = iconv("ISO-8859-1", "UTF-8", $row["Name"]);
            $defaultbranding = ($defaultbranding) ? $defaultbranding : "None";
            $defaultfirmware = $row["Version"];
            $defaultfirmware = ($defaultfirmware) ? $defaultfirmware : "None";

            $summary = array();
            $summary["Servers"] = $counts["Server"];
            $summary["Phones"] = $counts["Phone"];

            //$numunaff       = $counts["Unaffiliated"] + 0;        // Add + 0 to ensure there's a number there
            //$numunreg       = $counts["Unregistered"] + 0;        // (it's possible these are null)
            $numbrands      = $counts["Branding"] + 0;
            $nummaxbrands   = $counts["MaxBrandings"] + 0;

            $summary["Branding Sets"] = $numbrands . " used out of " . $nummaxbrands . " available";
            $summary["Default Branding"] = $defaultbranding;
            $summary["Default Firmware"] = $defaultfirmware;

            $vendorNameQuery = "SELECT Vendor.Name as vendorName FROM Vendor WHERE VendorID=$vid";
            MySQL::runQuery($vendorNameQuery);
            $row = MySQL::getNextRow();
            $resArr["Account summary for vendor - " . $row["vendorName"]] = $summary;
            $resArr["status code"] = 200;
            return json_encode($resArr);
        } else {
            throw new Exception("Details Not Found - check with the VendorID", 404);
        }
    }

    public static function deleteByPhoneId($data)
    {
        $data['phonemaclist'] = (isset($data['phonemaclist'])) ? $data['phonemaclist'] : "NULL";
        if ($data['phonemaclist'] == "") {
            throw new Exception("No Phones deleted.", 409);
        } else {
            $macs = explode(" ", $data['phonemaclist']);
            foreach ($macs as $mac) {
                $mac = util::cleanMacAddress($mac);
            }
            $num = 0;
            for ($i = 0; $i < count($macs); $i++) {
                $command = new DeleteCommand("Phone");
                $command->setParam("PhoneID", hexdec($macs[$i]));
                $execute = $command->execute();
                if ($execute) {
                    $num++;
                }
            }
            $resArr["message"] = ($num . (($num == 1) ? " phone has" : " phones have") . " been deleted.");
            return json_encode($resArr);
        }
    }


    //add phones
    public static function addPhone($data)
    {

        if (isset($data["VendorID"])) {
            $vendorid = $data["VendorID"];
        } else {
            throw new Exception("VendorID Needed ", 422);
        }
        $serverid = (isset($data["ServerID"])) ? $data["ServerID"] : "NULL";
        $branding = (isset($data["BrandingID"])) ? $data["BrandingID"] : "NULL";;
        $firmware = (isset($data["FirmwareID"])) ? $data["FirmwareID"] : "NULL";
        //$IPAddress = (isset($data["IPAddress"])) ? $data["IPAddress"] : "NULL";

        $data['addphonemaclist'] = (isset($data['addphonemaclist'])) ? $data['addphonemaclist'] : "NULL";


        //$userid = $_POST['userid']; ////////////////////////////////////////////////////////////look


        if ($data['addphonemaclist'] == "") {
            throw new Exception("No Phones added.", 409);
        } else {
            $errorCount = 0;
            $num = 0;
            //$macs = explode(";", rtrim($_POST['addphonemaclist_string'], ";"));
            $macs = explode(" ", $data['addphonemaclist']);
            foreach ($macs as $mac) {
                $mac = util::cleanMacAddress($mac);
            }
            foreach ($macs as $mac) {
                //$mac = Globals::cleanMacAddress($mac);
                if ($mac !== false) {

                    $existsQuery = "SELECT * FROM Phone WHERE PhoneID = '" . hexdec($mac) . "'";
                    MySQL::runQuery($existsQuery);

                    if (MySQL::getAffectedRows() >= 1) {
                        throw new Exception("MAC $mac already exists on the system.", 409);
                    } else {
                        $command = new AddCommand("Phone");
                        $command->setParam("PhoneID", hexdec($mac));
                        $command->setParam("VendorID", $vendorid);
                        $command->setParam("ServerID", $serverid);
                        $command->setParam("BrandingID", $branding);
                        $command->setParam("FirmwareID", $firmware);
                        //$command->setParam("AddedBy", $userid);
                        $command->setParam("DateAdded", MySQL::getCurrentDate());
                        try {
                            $command->execute();
                            $num++;
                        } catch (Exception $ex) {
                            $m = new Exception("Error adding $mac to database: " . $ex->getMessage());
                            return json_encode($m);
                        }
                    }
                } else {
                    $errorCount++;
                }
            }
            if ($num > 0) {
                $resArr["message"] = $num . (($num == 1) ? ' phone has' : ' phones have') . ' been added or updated';
                $resArr["status code"] = 201;
                return json_encode($resArr);
            } else {
                throw new Exception("Error adding $mac to database", 409);
            }
           
        }
    }

    public static function updatePhonesByPhoneId($pid, $data)
    {
        $query = "SELECT * FROM Phone WHERE PhoneID =$pid";
        MYSQL::runQuery($query);
        $row = MySQL::getNextRow();
        $VendorID = (isset($data["VendorID"])) ? $data["VendorID"] : $row["vendorID"];
        $ServerID = (isset($data["ServerID"])) ? $data["ServerID"] : $row["ServerID"];
        $BrandingID = (isset($data["BrandingID"])) ? $data["BrandingID"] : $row["BrandingID"];
        $FirmwareID = (isset($data["FirmwareID"])) ? $data["FirmwareID"] : $row["FirmwareID"];
        $IPAddress = (isset($data["IPAddress"])) ? $data["IPAddress"] : $row["IPAddress"];

        $query = "UPDATE Phone SET ServerID=$ServerID, VendorID=$VendorID,ServerID=$ServerID,
                        BrandingID=$BrandingID,FirmwareID=$FirmwareID,IPAddress=$IPAddress WHERE PhoneID=$pid";
        $res = MySQL::runQuery($query, $totalrows = null);

        if (MySQL::getAffectedRows() > 0) {
            $resArr["message"] = "phone updated";
            $resArr["status code"] = 200;
            return json_encode($resArr);
        } else {
            throw new Exception("Phone not updated - check with the PhoneID or try changing input data", 409);
        }
    }

    public static function getToken($pid)
    {
        $now = new DateTime();
        $future = new DateTime('+30 min');
        $secretKey = "secret_key";
        $payload = [
            "jti" => $pid,
            "iat" => $now,
            "exp" => $future
        ];
        $headers =  ['alg' => 'HS256', 'typ' => 'JWT'];
        $headers_encoded = util::base64url_encode(json_encode($headers));
        $payload_encoded = util::base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secretKey, true);
        $signature_encoded = util::base64url_encode($signature);
        $token = "$headers_encoded.$payload_encoded.$signature_encoded";

        $jwt = array();
        $jwt["JWT token"] = $token;
        $jwt['now'] = $now;
        $jwt['future'] = $future;
        //$jwt["Expires in"] = (($future-$now)/60)." minutes";

        return json_encode($jwt);
    }
}
