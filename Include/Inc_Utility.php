<?php
class Inc_Utility {
    static function clearFormat ($str, $needStripTags = TRUE, $needBreakLine = TRUE) {
        if (get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }
        $str = addslashes($str);
        $str = Inc_Utility::trimHtml($str);
        if ($needStripTags === TRUE) {
            if ($needBreakLine === TRUE) {
                $str = preg_replace('/[\r\n]+/', '', $str);
            }
            $str = strip_tags($str);
        }
        return $str;
    }
    static function clearFormatMce ($str) {
        return Inc_Utility::clearFormat($str, FALSE);
    }
    static function clearFormatTextArea ($str) {
        return Inc_Utility::clearFormat($str, TRUE, FALSE);
    }
    /**
     * use this function before insert/update to mongo
     */
    static function clearFormatMongo ($arr) {
        if (is_array($arr)) {
            foreach ($arr as &$item) {
                if (is_string($item) === TRUE) {
                    $item = stripslashes($item);
                }
            }
            unset($item);
        }
        return $arr;
    }
    static function trimHtml ($string) {
        $patterns = array();
        $patterns[0] = '#^(( ){0,}<br( {0,})(/{0,1})>){1,}#i';
        $patterns[1] = '#(( ){0,}<br( {0,})(/{0,1})>){1,}$#i';
        $patterns[2] = '/<\/li( {0,})>( {0,})<br( {0,})(\/{0,1})>/is';
        $patterns[3] = '/<ul( {0,})>( {0,})<br( {0,})(\/{0,1})>/is';
        $patterns[4] = '/<\/ul( {0,})>( {0,})<br( {0,})(\/{0,1})>/is';
        $replacements = array();
        $replacements[4] = '';
        $replacements[3] = '';
        $replacements[2] = '</li>';
        $replacements[1] = '<ul>';
        $replacements[0] = '</ul>';
        $string = preg_replace($patterns, $replacements, $string);
        return preg_replace('#(( ){0,}<br( {0,})(/{0,1})>){1,}$#i', '', trim($string));
    }
    static function getFullUrl () {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        return Inc_Utility::clearFormat($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }
    static function redirect ($url = null) {
        if (empty($url) === TRUE) {
            $url = Inc_Utility::getFullUrl();
        }
        ob_end_clean();
        header("Location: " . $url);
        exit();
    }
    static function getRedirectUrl () {
        return urlencode(urlencode($_SERVER['REQUEST_URI']));
    }
    static function returnRedirectUrl ($redirectUrl) {
        return urldecode(urldecode($redirectUrl));
    }
    static function getCurrentContext () {
        return array(
            'MODULE_NAME'     => $GLOBALS['MODULE_NAME'],
            'CONTROLLER_NAME' => $GLOBALS['CONTROLLER_NAME'],
            'ACTION_NAME'     => $GLOBALS['ACTION_NAME'],
            'PARAMS'          => $GLOBALS['PARAMS'],
            'URL'             => Inc_Utility::getFullUrl(),
            'HTTP_REFERER'    => @$_SERVER['HTTP_REFERER']
        );
    }
    static function writeMonolog ($logMessage, $context = array()) {
        if (empty($logMessage) === FALSE && isset($GLOBALS['monologError']) === TRUE) {
            $GLOBALS['monologError']->addError($logMessage, $context);
        }
    }
    static function throwError ($code = '404', $logMessage = NULL, $context = array()) {
        if (empty($context) === TRUE) {
            $context = Inc_Utility::getCurrentContext();
        }
        Inc_Utility::writeMonolog($logMessage, $context);
        switch ($code) {
            default:
                header('HTTP/1.0 404 Not Found');
                echo "<h1>404 Not Found</h1>";
                echo "The page that you have requested could not be found.";
        }
        exit();
    }
    /**
     * $name is name combobox
     * $arr is options: array(1 => 'hồ chí minh', '2' => 'hà nội')
     * $value is selected if match or 1|2|3
     * $extra is class or somethings
     */
    static function combobox ($name, $arr = array(), $value = NULL, $extra = '') {
        $result = '<select name=\'' . $name . '\' ' . $extra . '>';
        if (empty($arr) === FALSE) {
            if ($value !== NULL && is_string($value) === TRUE) {
                if (strpos($value, '|')) {
                    $value = explode('|', $value);
                }
            }
            foreach ($arr as $k => $v) {
                $result.= '<option value=\'' . $k . '\'';
                if ($value !== NULL) {
                    if (is_array($value) === TRUE) {
                        if (in_array($k, $value)) {
                            $result.= ' selected ';
                        }
                    } else if ($value == $k) {
                        $result.= ' selected ';
                    }
                }
                $result.= '>' . $v . '</option>';
            }
        }
        $result.= '</select>';
        return $result;
    }
    /**
     * get params page first, if don't have => get request page else get 1
     */
    static function getPage () {
        return intval(
            (empty($GLOBALS['PARAMS']['page']) === FALSE) ? $GLOBALS['PARAMS']['page'] :
            ((empty($_REQUEST['page']) === FALSE) ? $_REQUEST['page'] : 1)
        );
    }
    static function processModuleName ($name) {
        $result = array();
        foreach (explode('-', $name) as $item) {
            $result[] = ucwords($item);
        }
        return implode($result);
    }
    static function getErrors ($errors = array()) {
        $result = array();
        if (empty($errors) === FALSE) {
            $result[] = '<div class="alert">';
            $result[] = '<button type="button" class="close" data-dismiss="alert">×</button>';
            foreach ($errors as $k => $v) {
                $result[] = '<strong>';
                if (is_array($v)) {
                    foreach ($v as $e) {
                        $result[] = $e;
                    }
                } else {
                    $result[] = $v;
                }
                $result[] = '</strong><br>';
            }
            $result[] = '</div>';
        }
        return implode($result);
    }
    static function getFrontendErrors ($errors = array(), $frmName = NULL, $idError = 'myError', $header = 'Incorrect, please try again.', $textClose = 'Close') {
        $result = array();
        if (empty($errors[$frmName]) === FALSE) {
            $errors = $errors[$frmName];
        }
        if (empty($errors) === FALSE) {
            $result[] = '<div class="modal fade" id="' . $idError . '" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">';
            $result[] = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title label label-danger">' . $header . '</h4></div><!-- end modal-header -->';
            $result[] = '<div class="modal-body">';
            foreach ($errors as $k => $v) {
                $result[] = '<strong>- ';
                if (is_array($v)) {
                    foreach ($v as $e) {
                        $result[] = $e;
                    }
                } else {
                    $result[] = $v;
                }
                $result[] = '</strong><br>';
            }
            $result[] = '</div><!-- end modal-body -->';
            $result[] = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . $textClose . '</button></div><!-- end modal-footer -->';
            $result[] = '</div></div></div>';
            $result[] = '<script type="text/javascript">$(document).ready(function () { $("#' . $idError . '").modal(); });</script>';
        }
        return implode($result);
    }
    static function validateFile ($file, $types = array('image/gif', 'image/pjpeg', 'image/jpeg', 'image/bmp', 'image/png'), $maxSize = 4000000, $isReturnErrorCode = FALSE) {
        $error  = array();
        if (empty($file['tmp_name']) === FALSE) {
            $finfo = new finfo(FILEINFO_MIME);
            if (empty($types) === FALSE && is_array($types) === TRUE) {
                if (is_array($file["type"]) === TRUE) {
                    foreach ($file["type"] as $k => $item) {
                        if (empty($file["tmp_name"][$k]) === FALSE) {
                            $type = $finfo->file($file["tmp_name"][$k], FILEINFO_MIME_TYPE);
                            /**
                             * case: chrome, firefox... and safari because safari cannot detect via tmp extension
                             */
                            if (in_array($type, $types) === FALSE) {
                                $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"][$k] . ': file type not correct') : (1 . ':' . $file["name"][$k]);
                            }
                        }
                    }
                } else {
                    if (empty($file["tmp_name"]) === FALSE) {
                        $type = $finfo->file($file["tmp_name"], FILEINFO_MIME_TYPE);
                        /**
                         * case: chrome, firefox... and safari because safari cannot detect via tmp extension
                         */
                        if (in_array($type, $types) === FALSE) {
                            $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"] . ': file type not correct') : (1 . ':' . $file["name"]);
                        }
                    }
                }
            }
            if (is_array($file["size"]) === TRUE) {
                foreach ($file["size"] as $k => $item) {
                    if ($item > $maxSize) {
                        $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"][$k] . ': file input too large') : (2 . ':' . $file["name"][$k]);
                    }
                }
            } else if ($file["size"] > $maxSize) {
                $error[] = $isReturnErrorCode === FALSE ? ('File ' . $file["name"] . ': file input too large') : (2 . ':' . $file["name"]);
            }
            if (is_array($file["error"]) === TRUE) {
                foreach ($file["error"] as $k => $item) {
                    if ($item > 0 || empty($file["size"][$k]) === true) {
                        $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"][$k] . ': can\'t upload file') : (3 . ':' . $file["name"][$k]);
                    }
                }
            } else if ($file["error"] > 0 || empty($file["size"]) === true) {
                $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"] . ': can\'t upload file') : (3 . ':' . $file["name"]);
            }
            if (is_array($file["name"]) === TRUE) {
                foreach ($file["name"] as $k => $item) {
                    if (mb_strlen($item, 'utf-8') > 80) {
                        $error[] = ($isReturnErrorCode === FALSE) ? ('File ' . $file["name"][$k] . ': file name too long') : (4 . ':' . $file["name"][$k]);
                    }
                }
            } else if (mb_strlen($file["name"], 'utf-8') > 80) {
                $error[] = $isReturnErrorCode === FALSE ? ('File ' . $file["name"] . ': file name too long') : (4 . ':' . $file["name"]);
            }
        }
        return $error;
    }
    static function validateFileByExtension ($file, $types = array('.png', '.jpeg', '.jpg', '.pdf'), $maxSize = 4000000) {
        $error = array();
        if (empty($file['name']) === FALSE) {
            $path = $file['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, $types) == FALSE) {
                $error[] = 'File ' . $file["name"] . ': invalid type';
            }
            if ($file["size"] > $maxSize) {
                $error[] = 'File ' . $file["name"] . ': file input too large';
            }
            if ($file["error"] > 0) {
                $error[] = 'File ' . $file["name"] . ': can\'t upload file';
            }
        }
        return $error;
    }
    static function uploadImage ($from, $to) {
        move_uploaded_file($from , $to);
    }
    static function getNewFileName ($path, $fileName) {
        if (file_exists($path . $fileName) || $path === NULL) {
            $pictmp = "-" . time();
            $fileName = substr($fileName, 0, strrpos($fileName, ".")) . $pictmp . substr($fileName, strrpos($fileName, "."));
        }
        return $fileName;
    }
    /* remove: $memcache->flush(); */
    static function memCache ($cacheTime, $key, $model, $function) {
        global $memcache;
        $isGetData = TRUE;
        if (isset($memcache) === TRUE) {
            $result = $memcache->get($_SERVER['HTTP_HOST'] . '_'. $key);
            if ($result !== FALSE) {
                $isGetData = FALSE;
            }
        }
        if ($isGetData === TRUE) {
            // echo 'no cache';
            $arguments = array_reverse(func_get_args());
            $leng = count(func_get_args()) - 4;
            if ($leng > 0) {
                foreach ($arguments as $k => $item) {
                    if ($k < $leng) {
                        $arg = array();
                        for ($i = $k; $i < $leng; $i++) {
                            $arg[] = $arguments[$i];
                        }
                        $result = call_user_func_array(array($model, $function), array_reverse($arg));
                        break;
                    }
                }
            } else {
                $result = $model->$function();
            }
            if (isset($memcache) === TRUE) {
                $compress = is_bool($result) || is_int($result) || is_float($result) ? false : MEMCACHE_COMPRESSED;
                $memcache->set($_SERVER['HTTP_HOST'] . '_'. $key, $result, $compress, $cacheTime);
            }
        }
        return $result;
    }
    /* remove: $memcache->flush(); */
    static function memCacheXml ($cacheTime, $key) {
        global $memcache;
        $isGetData = TRUE;
        if (isset($memcache) === TRUE) {
            $result = $memcache->get($_SERVER['HTTP_HOST'] . '_' . $key);
            if ($result !== FALSE) {
                $isGetData = FALSE;
            }
        }
        if ($isGetData === TRUE) {
            $result = simplexml_load_file($key);
            if (isset($memcache) === TRUE) {
                $compress = is_bool($result) || is_int($result) || is_float($result) ? false : MEMCACHE_COMPRESSED;
                $memcache->set($_SERVER['HTTP_HOST'] . '_' . $key, json_decode(json_encode($result)), false, $cacheTime);
            }
        }
        return $result;
    }
    /**
     * Use: Inc_Utility::arraySearch($array, 'id == 1')
     */
    static function arraySearch ($array, $expression, $returnAll = TRUE) {
        $result     = array();
        if (empty($expression) === FALSE) {
            $expression = preg_replace ("/([^\s]+?)(=|<|>|!)/", "\$a['$1']$2", str_replace(array(
                ' ', '==='
            ), array(
                '', '=='
            ), $expression));
            foreach ($array as $a) if (eval ( "return $expression;" )) $result[] = $a;
            if ($returnAll === FALSE && empty($result) === FALSE) {
                $result = $result[0];
            }
        }
        return $result;
    }
    static function createFCK ($fckName, $fckValue, $fckToolbarSet = "MyToolbar", $fckWidth = "100%", $fckHeight = "300") {
        require_once BASE_FRONT_PATH . "/fckeditor/fckeditor.php";
        $oFCKeditor = new FCKeditor($fckName) ;
        $oFCKeditor->BasePath = BASE_FRONT . '/fckeditor/' ;
        $oFCKeditor->Config['EnterMode'] = 'br';
        $oFCKeditor->Config['AutoDetectLanguage'] = TRUE;
        $oFCKeditor->Config['DefaultLanguage'] = 'vi';
        $oFCKeditor->Config['ToolbarStartExpanded'] = FALSE;
        $oFCKeditor->Config['SkinPath'] = BASE_FRONT . '/fckeditor/skin/Office2007Real/' ;
        $oFCKeditor->Width      = $fckWidth;
        $oFCKeditor->Height     = $fckHeight;
        $oFCKeditor->ToolbarSet = $fckToolbarSet;
        $oFCKeditor->Value      = $fckValue;
        $oFCKeditor->Create();
    }
    static function parseLazyLoad ($html) {
        $doc = new DOMDocument(); // get dom
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        $imgs = $doc->getElementsByTagName('img'); // get all images
        if (count($imgs) > 0) {
            foreach ($imgs as $img) {
                /**
                 * get values
                 */
                $src = $img->getAttribute('src');
                $class = $img->getAttribute('class');
                $lazyClass = 'lazy';
                /**
                 * set values
                 */
                $img->setAttribute('src', BASE_FRONT . '/images/backend/grey.gif');
                $img->setAttribute('data-original', $src);
                if (empty($class) === FALSE) {
                    $class = str_replace(array(' lazy', 'lazy ', 'lazy'), NULL, $class); // remove lazy
                    $lazyClass .= " {$class}";
                }
                /**
                 * export => <img class="lazy" src="/images/backend/grey.gif" data-original="{src}" />
                 */
                $img->setAttribute('class', $lazyClass);
            }
        }
        return html_entity_decode($doc->saveHTML(), ENT_COMPAT | ENT_HTML5, 'UTF-8');
    }
    static function isHasValidRole ($uri, &$isHasChild = TRUE) {
        /**
         * fix bug absolute url, this case we need relative url
         */
        if (strpos($uri, BASE_URL) === 0) {
            $uri = BASE_FOLDER . str_replace(BASE_URL, NULL, $uri);
        }
        $validRole = array();
        $infoUrl = processUri($uri);
        $roleModel = new Model_Role();
        $resourceModel = new Model_Role_Resource();
        $role = $_SESSION['logged']['role'];
        $controller = Inc_Utility::processModuleName($infoUrl['CONTROLLER_NAME']);
        $action = lcfirst(Inc_Utility::processModuleName($infoUrl['ACTION_NAME']));
        $validRole['isValid'] = TRUE;
        $validRole['isCheckRole'] = TRUE;
        if ($role === '1') {
            $validRole['isCheckRole'] = FALSE;
        }
        if ($controller === 'System' && $action === 'logout') {
            $validRole['isCheckRole'] = FALSE;
        }
        if ($action === 'moveTop' || $action === 'moveBottom' || $action === 'mongoMoveTop' || $action === 'mongoMoveBottom') {
            $validRole['isCheckRole'] = FALSE;
        }
        if ($validRole['isCheckRole'] === TRUE) {
            /**
             * get detail role
             */
            $record = $roleModel->getOneById($role);
            /**
             * get list controllers and actions that user has
             */
            $allowed = unserialize($record[$roleModel->roles]);
            /**
             * get list controllers
             */
            $controllerAllowed = implode(', ', $resourceModel->getController($allowed));
            /**
             * get list actions
             */
            $actionAllowed = implode(', ', $resourceModel->getAction($allowed));
            /**
             * build sql
             */
            $from = "{$resourceModel->tableName} a inner join {$resourceModel->tableName} c on c.{$resourceModel->_id} = a.{$resourceModel->id_controller}";
            $where = "c.{$resourceModel->_id} in ($controllerAllowed) and c.{$resourceModel->name} = '{$controller}' and a.{$resourceModel->_id} in ($actionAllowed) and a.{$resourceModel->name} = '{$action}'";
            $select = array("a.*");
            $resourceModel->find($from, $where, NULL, NULL, $select);
            $controllerAllowed = $resourceModel->toRow();
            /**
             * if user not have permission => redirect user to login page
             */
            if (empty($controllerAllowed) === TRUE) {
                $validRole['isValid'] = FALSE;
            } else {
                $isHasChild = TRUE;
            }
        }
        return $validRole;
    }
    /**
     * only get nav once time
     */
    static function getAdminNav ($array) {
        if (empty($_SESSION['logged_nav']) === TRUE) {
            $result = array();
            foreach ($array as $k => $item) {
                $level1 = array();
                if (empty($item['children']) === TRUE) {
                    $validRole = Inc_Utility::isHasValidRole($item['href']);
                    if ($validRole['isValid'] === TRUE) {
                        $level1[$k] = '<li class="dropdown"><a href="' . $item['href'] . '">' . $item['title'] . '</a></li>';
                    }
                } else {
                    $level1[$k] = '<li class="dropdown"><a href="' . $item['href'] . '" class="dropdown-toggle" data-toggle="dropdown">' . $item['title'] . ' <b class="caret"></b></a>';
                    $isHasChild = FALSE;
                    $level2 = array('<ul class="dropdown-menu">');
                    foreach ($item['children'] as $l2) {
                        if (empty($l2['href']) === TRUE) {
                            if (empty($l2['title']) === TRUE) {
                                $level2[] = '<li class="divider"></li>';
                            } else {
                                $level2[] = '<li class="nav-header">' . $l2['title'] . '</li>';
                            }
                        } else {
                            $validRole = Inc_Utility::isHasValidRole($l2['href'], $isHasChild);
                            if ($validRole['isValid'] === TRUE) {
                                $level2[] = '<li><a href="' . $l2['href'] . '">' . $l2['title'] . '</a></li>';
                            }
                        }
                    }
                    $level2[] = '</ul>';
                    if ($validRole['isCheckRole'] === TRUE && $isHasChild === FALSE) {
                        unset($level1[$k]);
                    } else {
                        $level1[] = implode(NULL, $level2) . '</li>';
                    }
                }
                $result[] = implode(NULL, $level1);
            }
            $lastResult = array();
            foreach ($result as $items) {
                if (empty($items) === FALSE) {
                    $lastResult[] = $items;
                }
            }
            $_SESSION['logged_nav'] = implode('<li class="divider-vertical"></li>', $lastResult);
        }
        return $_SESSION['logged_nav'];
    }
    static function getSuffix ($suffix, $url = NULL, $defaultLanguage = DEFAULT_LANGUAGE) {
        $url = empty($url) === FALSE ? $url : rtrim(substr($_SERVER['REQUEST_URI'], strlen(BASE_FOLDER) + 1), '/');
        $uri = explode('/', $url);
        $endUri = end($uri);
        $result = NULL;
        if (strpos($endUri, '?') === 0) {
            $endUriParsed = array_merge(Inc_Utility::parseArrayUri(ltrim($endUri, '?')), Inc_Utility::parseArrayUri($suffix));
            $newSuffix = array();
            if (empty($endUriParsed) === FALSE) {
                foreach ($endUriParsed as $k => $v) {
                    $newSuffix[] = $k . '=' . $v;
                }
                $result = '/?' . implode('&', $newSuffix);
            }
        } else {
            $result = '/?' . $suffix;
        }
        $result = str_replace(array('&lang=' . $defaultLanguage, '?lang=' . $defaultLanguage, '/?&'), array(NULL, '?', '/?'), $result);
        if ($result === '/?') {
            $result = NULL;
        }
        return $result;
    }
    /**
     * use this function for site more than one language
     * $suffix = 'lang=vn&gender=male'
     */
    static function addSuffix ($suffix, $url = NULL) {
        $url = empty($url) === FALSE ? $url : rtrim(substr($_SERVER['REQUEST_URI'], strlen(BASE_FOLDER) + 1), '/');
        $uri = explode('/', $url);
        $endUri = end($uri);
        if (strpos($endUri, '?') === 0) {
            $url = substr($url, 0, strpos($url, '/?')) . Inc_Utility::getSuffix($suffix, $url);
        } else {
            $url .= Inc_Utility::getSuffix($suffix, $url);
        }
        return rtrim(ltrim($url, '/'), '?');
    }
    static function parseArrayUri ($suffix) {
        $parse = array();
        if (empty($suffix) === FALSE) {
            $array = explode('&', $suffix);
            if (empty($array) === FALSE) {
                foreach ($array as $item) {
                    $arr = explode('=', $item);
                    $parse[$arr[0]] = $arr[1];
                }
            }
        }
        return $parse;
    }
    /**
     * $type: skype
     * $nick: songpham88 or minhhuyit
     * return 1: availlable, 2: invisible
     */
    static function checkSocialOnline ($type, $nick) {
        switch ($type) {
            case 'skype':
                $data = file_get_contents('http://mystatus.skype.com/' . urlencode($nick) . '.xml');
                return strpos($data, '<presence xml:lang="NUM">2</presence>') ? 1 : 0;
                break;
        }
        return NULL;
    }
    static function newUrlEncode ($url) {
        $url = str_replace(
            array(' '),
            array('abcd'),
            $url
        );
        return str_replace(
            array('%3A', '%2F', '%3F', '%3D', '%26', '%5B', '%5D', 'abcd'),
            array(':', '/', '?', '=', '&', '[', ']', '%20'),
            urlencode($url)
        );
    }
    /**
     * You can get cURL to only give you the headers, and not the body, which might make it faster.
     * A bad domain could always take a while because you will be waiting for the request to time-out;
     * You could probably change the timeout length using cURL
     */
    static function remoteFileExists ($url) {
        if (strpos(baseName($url), '.') === FALSE) // return false if folder
            return FALSE;
        $curl = curl_init($url);
        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);
        //do request
        $result = curl_exec($curl);
        $ret = FALSE;
        //if request did not fail
        if ($result !== FALSE) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $ret = true;
            }
        }
        curl_close($curl);
        return $ret;
    }
    /**
     * browse remote folder
     */
    static function get_text ($filename) {
        $fp_load = fopen($filename, "rb");
        if ($fp_load) {
            $content = NULL;
            while (!feof($fp_load)) {
                $content .= fgets($fp_load, 8192);
            }
            fclose($fp_load);
            return $content;
        }
    }
    static function browseRemoteFolder ($pathXml) {
        $matches = $result = array();
        preg_match_all("/(a href\=\")([^\?\"]*)(\")/i", Inc_Utility::get_text($pathXml), $matches);
        foreach($matches[2] as $k => $match) {
            if ($k > 0) {
                $result[] = $match;
            }
        }
        return $result;
    }
    static function removeFirstItemInArray ($array) {
        if (is_array($array) === TRUE) {
            reset($array);
            $key = key($array);
            unset($array[$key]);
            $array = array_values($array);
        }
        return $array;
    }
    static function setCookie ($cookieName, $value, $timeout = NULL, $domain = DOMAIN) {
        if (empty($timeout) === TRUE) {
            $timeout = TIMEOUT_COOKIE;
        }
        setcookie(PRE_COOKIE . $cookieName, $value, time() + $timeout, "/", $domain);
    }
    static function removeCookie ($cookieName, $domain = DOMAIN) {
        if (empty($_COOKIE[PRE_COOKIE . $cookieName]) === FALSE && is_array($_COOKIE[PRE_COOKIE . $cookieName])) {
            foreach ($_COOKIE[PRE_COOKIE . $cookieName] as $cookieKey => $cookieValue) {
                setcookie(PRE_COOKIE . $cookieName .'[' . $cookieKey . ']', '', -1, "/", $domain);
            }
        } else {
            setcookie(PRE_COOKIE . $cookieName, '', -1, "/", $domain);
        }
    }
    static function getCookie ($cookieName) {
        return isset($_COOKIE[PRE_COOKIE . $cookieName]) ? $_COOKIE[PRE_COOKIE . $cookieName] : NULL;
    }
    static function convertToTimestamp ($txt) {
        return is_numeric($txt) === TRUE ? $txt : strtotime($txt);
    }
    static function buildLink ($arrAdd = array(), $arrRemove = array()) {
        $result = NULL;
        $isHasColumn = array();
        if (isset($GLOBALS['URL_PARAMS']) === TRUE) {
            foreach ($GLOBALS['URL_PARAMS'] as $k => $v) {
                foreach ($arrAdd as $k1 => $v1) {
                    if ($k1 == $k) {
                        $v = $v1;
                        $isHasColumn[$k] = TRUE;
                    }
                }
                if (in_array($k, $arrRemove) === FALSE) {
                    $result .= $k . $v;
                }
            }
        }
        foreach ($arrAdd as $k => $v) {
            if (isset($isHasColumn[$k]) === FALSE) {
                $result .= $k . $v;
            }
        }
        return $result;
    }
}
