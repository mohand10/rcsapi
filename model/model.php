<?php
include_once __DIR__ . './../../include/MySQL.php';
require __DIR__ . './../services/rcsService.php';
require __DIR__ . './../utils/util.php';
require __DIR__ . './../include/Command.php';
require __DIR__ . './../include/AddCommand.php';
require __DIR__ . './../include/DeleteCommand.php';
require __DIR__ . './../include/EditCommand.php';
MySQL::connect();
class RcsModel implements RcsApiService
{    //const $IPAddress = $_SERVER['REMOTE_ADDR'];

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
            return $resArr;
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
                "PhoneID" => $row["PID"],
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
            return $resArr;
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
            return $resArr;
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

            //$numbrands      = $counts["Branding"] + 0;
            //$nummaxbrands   = $counts["MaxBrandings"] + 0;

            $summary["Branding Sets"] = $counts["Branding"] . " used out of " . $counts["MaxBrandings"] . " available";
            $summary["Default Branding"] = $defaultbranding;
            $summary["Default Firmware"] = $defaultfirmware;

            $vendorNameQuery = "SELECT Vendor.Name as vendorName FROM Vendor WHERE VendorID=$vid";
            MySQL::runQuery($vendorNameQuery);
            $row = MySQL::getNextRow();
            $resArr["status code"] = 200;
            $resArr["Account summary for vendor - " . $row["vendorName"]] = $summary;
            return $resArr;
        } else {
            throw new Exception("Details Not Found - check with the VendorID", 404);
        }
    }

    public static function deletePhone($data)
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
        }
        $resArr["message"] = ($num . (($num == 1) ? " phone has" : " phones have") . " been deleted.");
        return $resArr;
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


        $data['addphonemaclist'] = (isset($data['addphonemaclist'])) ? $data['addphonemaclist'] : "NULL";


        //$userid = $_POST['userid']; ////////////////////////////////////////////////////////////look


        if ($data['addphonemaclist'] == "") {
            throw new Exception("No Phones added.", 409);
        } else {
            $errorCount = 0;
            $num = 0;
            $macs = explode(" ", $data['addphonemaclist']);
            foreach ($macs as $mac) {
                $mac = util::cleanMacAddress($mac);
            }
            foreach ($macs as $mac) {
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
                        $command->setParam("IPAddress", $_SERVER['REMOTE_ADDR']);
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
                $resArr["status code"] = 201;
                $resArr["message"] = $num . (($num == 1) ? ' phone has' : ' phones have') . ' been added or updated';
                return $resArr;
            } else {
                throw new Exception("Error adding $mac to database", 409);
            }
        }
    }

    public static function updatePhone($data)
    {
        $data['phonemaclist'] = (isset($data['phonemaclist'])) ? $data['phonemaclist'] : "NULL";
        if ($data['phonemaclist'] == "") {
            throw new Exception("No Phones updated.", 409);
        } else {
            $macs = explode(" ", $data['phonemaclist']);
            foreach ($macs as $mac) {
                $mac = util::cleanMacAddress($mac);
            }
            $num = 0;
            foreach ($macs as $mac) {
                $query = "SELECT * FROM Phone WHERE PhoneID = 0x$mac";
                MySQL::runQuery($query);
                $row = MySQL::getNextRow();
                //$VendorID = (isset($data["VendorID"])) ? $data["VendorID"] : $row["vendorID"];
                $ServerID = (isset($data["ServerID"])) ? $data["ServerID"] : $row["ServerID"];
                $BrandingID = (isset($data["BrandingID"])) ? $data["BrandingID"] : $row["BrandingID"];
                $FirmwareID = (isset($data["FirmwareID"])) ? $data["FirmwareID"] : $row["FirmwareID"];
                //$IPAddress = (isset($data["IPAddress"])) ? $data["IPAddress"] : $row["IPAddress"];

                if ($mac !== false) {
                    $command = new EditCommand("Phone");
                    $command->setID(hexdec($mac));
                    $command->setParam("ServerID", $ServerID);
                    $command->setParam("BrandingID", $BrandingID);
                    $command->setParam("FirmwareID", $FirmwareID);
                    $command->setParam("IPAddress", $_SERVER['REMOTE_ADDR']);
                    //$command->setParam("ReferenceID", $_POST['referenceID']);
                    $execute = $command->execute();
                    if ($execute) {
                        $num++;
                    }
                }
            }
            $resArr["status code"] = 200;
            if ($num == 0) {
                $resArr["message"] = "No phones have been modified.";
            } else if ($num == 1) {
                $resArr["message"] = "1 phone has been modified.";
            } else {
                $resArr["message"] = "$num phones have been modified.";
            }
        }
        return $resArr;
    }


    //jwt token generation
    public static function getToken($pid)
    {
        //$now = new DateTime();
        $now = time();
        $future = strtotime('+30 min', $now);
        $secretKey = "Rcs@api123";
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
        return $jwt;
    }

    //private $user_name='';
    public static function login($data)
    {
        $data['username'] = isset($data['username']) ? $data['username'] : '';
        $data['password'] = isset($data['password']) ? $data['password'] : '';
        //$dat = RcsModel::getToken();

        MySQL::connect();
        $un = MySQL::cleanString($data['username']);
        $this->user_name = $un;
        $pw = md5(MySQL::cleanString($data['password']));
        $raw_pwd = MySQL::cleanString($data['password']);
        $reverseSalt = "$" . strrev(explode("@", $un)[0]) . "$";
        $pw256 = hash('sha256', $raw_pwd . $reverseSalt);
        $q = "SELECT User.UserID, User.VendorID, User.Username, User.Name, User.Rights, User.Eula, Vendor.Name AS VendorName, Vendor.NumBrandings, Vendor.CanFirmware " .
            ",DATEDIFF(CURDATE(),User.lastModified) as expiryDays, User.loginAttempts,TIMESTAMPDIFF(MINUTE,lastLoginTime,CURRENT_TIMESTAMP()) as locked ,isMD5     " . // Added new columns
            "FROM User LEFT JOIN Vendor ON (Vendor.VendorID=User.VendorID) WHERE Username='$un' AND Password in ('$pw','$pw256')";

        MySQL::runQuery($q);
        if (MySQL::getAffectedRows() == 1) {

            $row = MySQL::getNextRow();
            //return json_encode($row);
            $userid =  $row['UserID'];
            if ($row['loginAttempts'] >= 3) {
                if ($row['locked'] > 15) {
                    $updateUser = "Update User set loginAttempts=0,lastLoginTime=CURRENT_TIMESTAMP() where Username='" . $un . "'";
                    MySQL::runQuery($updateUser);
                } else if ($row['locked'] <= 15) {
                    $value = 15 - $row['locked'];
                    if ($value >= 15) {
                        $value = 15;
                    }
                    if ($value == 0) {
                        $value = 1;
                    }
                    throw new Exception("Account is Locked!! <br/> Please try after " . ($value) . " minutes.", 404);
                }
            }

            $_SESSION['userid'] = $userid;
            $_SESSION['username'] = $un;
            $_SESSION["login_time_stamp"] = time();

            if ($row['expiryDays'] >= 45) {
                if ($row['isMD5'] == 0) {
                    $updateUser = "Update User set loginAttempts=0,lastLoginTime=CURRENT_TIMESTAMP(),password ='" . $pw256 . "' where Username='" . $un . "'";
                    MySQL::runQuery($updateUser);
                } elseif ($row['isMD5'] == 1) {
                    $updateUser = "Update User set loginAttempts=0,lastLoginTime=CURRENT_TIMESTAMP() where Username='" . $un . "'";
                    MySQL::runQuery($updateUser);
                }
                $resArr['message'] = "Password expired, please reset it.";
                $updateUser = "Update User set loginAttempts=0,lastLoginTime=CURRENT_TIMESTAMP() where Username='" . $un . "'";
                MySQL::runQuery($updateUser);
            } else {
                $query = "INSERT INTO LoginAudits (UserID, ActionName, ActionTime) VALUES('$userid','Successfully Login',CURRENT_TIMESTAMP())";
                MySQL::runQuery($query);
                $resArr['status code'] = 200;
                $resArr['message'] = "Successful login & password expires in " . (45 - $row['expiryDays']) . " days";
                $resArr['token'] = (self::getToken($un));
            }
        } else {
            throw new Exception("Invalid credentials", 404);
        }
        return $resArr;
    }
}
