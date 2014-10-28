<?php
class Inc_Json {
    static function getJsonError ($error) {
        $err = '';
        switch ($error) {
            case JSON_ERROR_NONE:
                $err = ' - No errors';
            break;
            case JSON_ERROR_DEPTH:
                $err = ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $err = ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $err = ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $err = ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $err = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $err = ' - Unknown error';
            break;
        }
        return $err;
    }
    static function convertJsonToPhpArray ($json) {
        if (is_string($json) === TRUE) {
            /**
             * remove mongoObject
             */
            $json = preg_replace(
                array('/ObjectId\((\"[a-zA-Z0-9]+\")\)/', '/NumberInt\((\d)+\)/'),
                '$1',
                $json
            );
            $var = json_decode($json, TRUE);
            $error = json_last_error();
            if (empty($error) === FALSE) {
                return Inc_Json::getJsonError($error);
            } else {
                $result = preg_replace(
                    array('/Array\s+\(/', '/\[(\d+)\] => (.*)\n/', '/\[([^\d].*)\] => (.*)\n/'),
                    array('array (', '\1 => \'\2\''."\n", '  \'\1\' => \'\2\''."\n"),
                    substr(print_r($var, true), 0, -1)
                );
                $result = strtr($result, array('array ('=>'array('));
                $result = strtr($result, array("=> 'array('"=>'=> array('));
                $result = strtr($result, array(")\n\n"=>")\n"));
                $result = strtr($result, array("'\n"=>"',\n", ")\n"=>"),\n"));
                $result = preg_replace(array('/\n +/e'), array('strtr(\'\0\', array(\'    \'=>\'  \'))'), $result);
                $result = strtr($result, array(" Object',"=>" Object'<-"));
                return $result;
            }
        }
        return FALSE;
    }
}
