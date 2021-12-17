<?php
class util
{
    public static function cleanMacAddress($mac)
    {
        $tmpmac = preg_replace("/([\dA-F]{2}).([\dA-F]{2}).([\dA-F]{2}).([\dA-F]{2}).([\dA-F]{2}).([\dA-F]{2})/", "$1$2$3$4$5$6", strtoupper($mac));
        $tmp = "0x$tmpmac";
        // This will detect if there are non-hex numbers
        // in the string and fail if there is.
        if (!ctype_xdigit($tmpmac)) {
            throw new Exception("Enter valid MAC ID", 422);
        }

        // Ensure that the length is 12 and begins
        // with the Aastra/Mitel prefix.
        if ((strlen($tmpmac) == 12) &&
            ((substr($tmpmac, 0, 6) == "00085D") ||
                (substr($tmpmac, 0, 6) == "08000F")  ||
                (substr($tmpmac, 0, 6) == "00087B")  ||
                (substr($tmpmac, 0, 6) == "003042")  ||
                (substr($tmpmac, 0, 6) == "0090F8")  ||
                (substr($tmpmac, 0, 5) == "001B1")   ||
                (substr($tmpmac, 0, 5) == "001C1")   ||
                (substr($tmpmac, 0, 5) == "001F1")   ||
                (substr($tmpmac, 0, 6) == "1400E9"))
        ) {
            return $tmp;
        }

        // Otherwise, we fail.
        else {
            throw new Exception("Enter valid MAC ID - " . $tmpmac . " is not valid", 422);
        }
    }

    public static function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
