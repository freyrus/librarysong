<?php
class Inc_String {
    static function removeAccents ($str) {
        return Inc_String::removeStrangeCharacter(Inc_String::isoConvert($str));
    }
    static function removeStrangeCharacter ($str) {
        if(empty($str) === FALSE) {
            $arrStrangeCharacter = array(' ',':','*',',','&','^','%','$','#','@','!','"',"'",'“','”',"/",")","(",">","<","?",";",",","+","*","=",'[',']');
            return str_replace($arrStrangeCharacter, '-', $str);
        }
    }
    static function showChuoi ($str) {
        return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
    }
    static function showChuoiTextArea ($str) {
        return preg_replace('/[\r\n]+/', '<br/>', $str);
    }
    static function isoConvert ($str) {
        if(empty($str) === FALSE) {
            $unicode = array(
                'á'=>array('a'),
                'à'=>array('a'),
                'ả'=>array('a'),
                'ã'=>array('a'),
                'ạ'=>array('a'),
                'ă'=>array('a'),
                'ắ'=>array('a'),
                'ằ'=>array('a'),
                'ặ'=>array('a'),
                'ẳ'=>array('a'),
                'ẵ'=>array('a'),
                'â'=>array('a'),
                'ấ'=>array('a'),
                'ầ'=>array('a'),
                'ậ'=>array('a'),
                'ẩ'=>array('a'),
                'ẫ'=>array('a'),
                'Á'=>array('A'),
                'À'=>array('A'),
                'Ả'=>array('A'),
                'Ã'=>array('A'),
                'Ạ'=>array('A'),
                'Ă'=>array('A'),
                'Ắ'=>array('A'),
                'Ằ'=>array('A'),
                'Ẳ'=>array('A'),
                'Ẵ'=>array('A'),
                'Ặ'=>array('A'),
                'Â'=>array('A'),
                'Ấ'=>array('A'),
                'Ầ'=>array('A'),
                'Ẩ'=>array('A'),
                'Ẫ'=>array('a'),
                'Ậ'=>array('a'),
                'đ'=>array('d'),
                'Đ'=>array('D'),
                'é'=>array('e'),
                'è'=>array('e'),
                'ẻ'=>array('e'),
                'ẽ'=>array('e'),
                'ẹ'=>array('e'),
                'ê'=>array('e'),
                'ế'=>array('e'),
                'ề'=>array('e'),
                'ể'=>array('e'),
                'ễ'=>array('e'),
                'ệ'=>array('e'),
                'É'=>array('E'),
                'È'=>array('E'),
                'Ẻ'=>array('E'),
                'Ẽ'=>array('E'),
                'Ẹ'=>array('E'),
                'Ê'=>array('E'),
                'Ế'=>array('E'),
                'Ề'=>array('E'),
                'Ễ'=>array('E'),
                'Ể'=>array('E'),
                'Ệ'=>array('E'),
                'í'=>array('i'),
                'ì'=>array('i'),
                'ỉ'=>array('i'),
                'ĩ'=>array('i'),
                'ị'=>array('i'),
                'Í'=>array('I'),
                'Ì'=>array('I'),
                'Ỉ'=>array('I'),
                'Ĩ'=>array('I'),
                'Ị'=>array('I'),
                'ó'=>array('o'),
                'ò'=>array('o'),
                'ỏ'=>array('o'),
                'õ'=>array('o'),
                'ọ'=>array('o'),
                'ô'=>array('o'),
                'ố'=>array('o'),
                'ồ'=>array('o'),
                'ổ'=>array('o'),
                'ỗ'=>array('o'),
                'ộ'=>array('o'),
                'ơ'=>array('o'),
                'ớ'=>array('o'),
                'ờ'=>array('o'),
                'ở'=>array('o'),
                'ỡ'=>array('o'),
                'ợ'=>array('o'),
                'Ó'=>array('O'),
                'Ò'=>array('O'),
                'Ỏ'=>array('O'),
                'Õ'=>array('O'),
                'Ọ'=>array('O'),
                'Ô'=>array('O'),
                'Ố'=>array('O'),
                'Ồ'=>array('O'),
                'Ổ'=>array('O'),
                'Ỗ'=>array('O'),
                'Ộ'=>array('O'),
                'Ơ'=>array('O'),
                'Ớ'=>array('O'),
                'Ờ'=>array('O'),
                'Ở'=>array('O'),
                'Ỡ'=>array('O'),
                'Ợ'=>array('O'),
                'ú'=>array('u'),
                'ù'=>array('u'),
                'ủ'=>array('u'),
                'ũ'=>array('u'),
                'ụ'=>array('u'),
                'ư'=>array('u'),
                'ứ'=>array('u'),
                'ừ'=>array('u'),
                'ử'=>array('u'),
                'ữ'=>array('u'),
                'ự'=>array('u'),
                'Ú'=>array('U'),
                'Ù'=>array('U'),
                'Ủ'=>array('U'),
                'Ũ'=>array('U'),
                'Ụ'=>array('U'),
                'Ư'=>array('U'),
                'Ứ'=>array('U'),
                'Ừ'=>array('U'),
                'Ử'=>array('U'),
                'Ữ'=>array('U'),
                'Ự'=>array('U'),
                'ý'=>array('y'),
                'ỳ'=>array('y'),
                'ỷ'=>array('y'),
                'ỹ'=>array('y'),
                'ỵ'=>array('y'),
                'Ý'=>array('Y'),
                'Ỳ'=>array('Y'),
                'Ỷ'=>array('Y'),
                'Ỹ'=>array('Y'),
                'Ỵ'=>array('Y')
            );
            foreach($unicode as $nonUnicode=>$uni){
                $str = str_replace($nonUnicode, $uni['0'], $str);
            }
            return $str;
        }
    }
    static function showThumbResized ($filePath) {
        return str_replace(BASE_FRONT . '/', BASE_FRONT . '/' . RESIZE_FOLDER . '-', $filePath);
    }
    static function unicodeConvert($str, $random = FALSE){
        $str = mb_convert_case($str, MB_CASE_LOWER, "UTF-8");
        if (!$str) return false;
        $unicode = array(
            'a' => array('á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ặ', 'ằ', 'ẳ', 'ẵ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'),
            'A' => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ặ', 'Ằ', 'Ẳ', 'Ẵ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ'),
            'd' => array('đ'),
            'D' => array('Đ'),
            'e' => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'),
            'E' => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ'),
            'i' => array('í', 'ì', 'ỉ', 'ĩ', 'ị'),
            'I' => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị'),
            'o' => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'),
            '0' => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ'),
            'u' => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'),
            'U' => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự'),
            'y' => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'),
            'Y' => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ'),
            '-' => array(' ', ':', '*', ',', '&', '^', '%', '$', '#', '@', '!', '"', '.', "'", '“', '”', '/', ')', '(', '>', '<', '?', ';', ',', '+', '*', '=', '[', ']', '–', '_', '|')
        );
        foreach ($unicode as $nonUnicode => $uni) {
            foreach ($uni as $value) {
                $str = str_replace($value, $nonUnicode, $str);
            }
        }
        if ($random === TRUE) {
            return $str . "-" . rand(10000,99999);
        } else {
            return $str;
        }
    }
    static function classify ($word, $dash = '-') {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', $dash . '$1', $word));
    }
    static function showThumbnail ($img, $str) {
        return str_replace("{$str}/", "resized-{$str}/", $img);
    }
    static function priceNumber ($n) {
        // first strip any formatting;
        $n = 0 + str_replace(",", "", $n);
        // is this a number?
        if (!is_numeric($n)) return FALSE;
        return number_format($n, 0, ',', '.');
    }
    static function getNiceUrl ($name) {
        return preg_replace('/\-+/', '-', ltrim(rtrim(Inc_String::unicodeConvert($name), '-'), '-'));
    }
    static function countWord ($str) {
        $sub = explode(" ", $str);
        return count($sub);
    }
    static function numWord ($str, $n = 1) {
        $result = NULL;
        if (Inc_String::countWord($str) <= $n) {
            return $str;
        }
        $sub = explode(" ", $str);
        for ($i = 0; $i < $n; $i++) {
            $result .= $sub[$i] . " ";
        }
        return $result . "...";
    }
    static function limitString ($str, $n = 1) {
        if (mb_strlen($str, 'utf-8') <= $n) {
            return $str;
        }
        return mb_substr($str, 0, $n, 'utf-8') . '...';
    }
    static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    /**
     * http://www.php.net/manual/en/function.str-pad.php
     */
    static function fillSpace ($str, $length = 3, $fillUp = 0, $type = STR_PAD_LEFT) {
        return str_pad($str, $length, $fillUp, $type);
    }
    /**
     * replace
     *     iframe/shortstack/form/idCampaign/3619122...
     * To
     *     iframe/shortstack/form-visa/idCampaign/3619122
     * By pass:
     *     formVisa
     */
    static function reaplaceCurrentUrl ($actionName = NULL) {
        $url   = array();
        $url[] = $GLOBALS['MODULE_NAME'];
        $url[] = Inc_String::classify($GLOBALS['CONTROLLER_NAME']);
        if (empty($actionName) === FALSE) {
            $url[] = Inc_String::classify($actionName);
        } else {
            $url[] = Inc_String::classify($GLOBALS['ACTION_NAME']);
        }
        if (empty($GLOBALS['PARAMS']) === FALSE) {
            $urlParams = array();
            foreach ($GLOBALS['PARAMS'] as $k => $item) {
                $urlParams[] = $k . '/' . $item;
            }
            $url[] = implode('/', $urlParams);
        }
        /**
         * fix bug QUERY_STRING: /?q=...
         */
        if (empty($_SERVER['QUERY_STRING']) === FALSE && strpos($_SERVER['QUERY_STRING'], 'q=') !== 0) {
            $url[] = ('?' . $_SERVER['QUERY_STRING']);
        }
        return implode('/', $url);
    }
    /**
     * input url: https://images.cfyc.com.vn/upload...
     * output url: images.cfyc.com.vn/upload...
     */
    function removePartOfUrl ($url, $part = array('scheme')) {
        $arrDomain = parse_url($url);
        $domain = array();
        if (empty($arrDomain) === FALSE) {
            foreach ($arrDomain as $k => $item) {
                if (in_array($k, $part) === FALSE) {
                    if ($k === 'scheme') {
                        $item .= '://';
                    }
                    $domain[] = $item;
                }
            }
        }
        return $domain = implode(NULL, $domain);
    }
    /**
     * fix bug REDIRECT_URL return include QUERY_STRING. Use for handle REDIRECT_URL
     */
    static function trimQueryString ($url) {
        return str_replace('/?' . $GLOBALS['_SERVER']['QUERY_STRING'], NULL, $url);
    }
    /**
     * convert from: song http://www.baomoi.vn www.tuoitre.vn
     * to: song <a href="http://www.baomoi.vn" target="_blank" rel="nofollow">http://www.baomoi.vn</a> <a href="http://www.baomoi.vn" target="_blank" rel="nofollow">http://www.tuoitre.vn</a>
     */
    static function plainUrlToLink ($text, $extra = 'target="_blank" rel="nofollow"') {
        if (empty($text) === FALSE) {
            // force http: on www.
            $text = str_replace("www.", "http://www.", $text);
            // eliminate duplicates after force
            $text = str_replace("http://http://www.", "http://www.", $text);
            $text = str_replace("https://http://www.", "https://www.", $text);
            // The Regular Expression filter
            $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
            // The Text you want to filter for urls, Check if there is a url in the text
            if (preg_match_all($reg_exUrl, $text, $url)) { /* make the urls hyper links */
                $matches = array_unique($url[0]);
                foreach ($matches as $match) {
                    $replacement = "<a href=" . $match . " " . $extra . ">{$match}</a>";
                    $text = str_replace($match, $replacement, $text);
                }
                return nl2br($text);
            } else { /* if no urls in the text just return the text */
                return nl2br($text);
            }
        }
        return $text;
    }
}
