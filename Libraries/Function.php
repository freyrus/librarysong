<?php
// setup __autoload
$composerPath = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerPath) === TRUE) {
    $GLOBALS["composer"] = include_once $composerPath;
}
spl_autoload_register(function ($name) {
    if ($name === 'Models') {
        require_once LIB_PATH . '/Libraries/' . $name . ".php";
    } else if (strpos($name, 'Model') === 0) {
        if (defined('BASE_CONFIG') === TRUE) {
            require_once BASE_CONFIG . '/Class/' . $name . ".php";
        } else {
            require_once BASE_PATH . '/Class/' . $name . ".php";
        }
    } else if (strpos($name, 'Inc') === 0) {
        require_once LIB_PATH . '/Include/' . $name . ".php";
    } else if (strpos($name, 'Mongo') === 0) {
        require_once BASE_PATH . '/Class_Mongo/' . $name . ".php";
    } else if (strpos($name, 'Mixins') === 0) { // support trails
        require_once BASE_PATH . '/Modules/' . $GLOBALS['MODULE_NAME'] . '/Common/' . $name . ".php";
    } else if (strstr(strstr($name, '_'), 'Controller') === 'Controller') { // support controllers
        $moduleName = Inc_String::classify(substr($name, 0, strpos($name, '_')));
        $controllerName = substr($name, strpos($name, '_') + 1);
        require_once BASE_PATH . '/Modules/' . $moduleName . '/Controllers/' . $controllerName . '.php';
    } else if (empty($GLOBALS['MODULE_NAME']) === FALSE && is_file(BASE_PATH . '/Modules/' . $GLOBALS['MODULE_NAME'] . '/' . $name . ".php")) {
        require_once BASE_PATH . '/Modules/' . $GLOBALS['MODULE_NAME'] . '/' . $name . ".php";
    }
});
// initRouter
function processUri ($uri) {
    $result['MODULE_NAME']     = 'default';
    $result['CONTROLLER_NAME'] = 'index';
    $result['ACTION_NAME']     = 'index';
    $result['PARAMS']          = array();
    $result['VIEWS']           = array();
    $uri = explode('/', substr($uri, strlen(BASE_FOLDER) + 1));
    if (empty($uri) === FALSE) {
        $p1 = 0;
        $p2 = 1;
        $fv = 1;
        /**
         * remove uri redundance
         */
        $router = array();
        $test='/^\?/';
        foreach ($uri as $item) {
            if(preg_match($test, $item) === 0) {
                $router[] = $item;
            }
        }
        /**
         * default router: Custom router
         */
        $isUseRouter = FALSE;
        if (
            empty($router[$p1]) === FALSE &&
            is_file(BASE_PATH .  '/Modules/' . $result['MODULE_NAME'] .  '/Controllers/' . Inc_Utility::processModuleName($router[$p1]) . 'Controller.php') === FALSE
        ) {
            if (defined('BASE_CONFIG') === TRUE) {
                require_once BASE_CONFIG . '/router.php';
            } else {
                require_once BASE_PATH . '/router.php' ;
            }
            $urlString = $GLOBALS['URLSTRING'] = rtrim(implode('/', $router), '/');
            if (empty($listRouter) === FALSE) {
                foreach ($listRouter as $k => $v) {
                    if (strpos($k, '/') === 0) { // regular express
                        preg_match($k, $urlString, $matches);
                    } else {
                        $matches = ($k === $urlString);
                    }
                    if (empty($matches) === FALSE) {
                        $isUseRouter = TRUE;
                        $v['CONTROLLER_NAME'] = Inc_Utility::processModuleName($v['CONTROLLER_NAME']);
                        $v['ACTION_NAME'] = Inc_Utility::processModuleName($v['ACTION_NAME']);
                        if (empty($v['PARAMS']) === FALSE) {
                            /**
                             * $p is param. This function will support regular expression for router
                             */
                            $vResult = array();
                            foreach ($v['PARAMS'] as $k1 => $p) {
                                $vResult[$k1] = $p;
                                if (is_array($matches) === TRUE && strpos($p, ':') === 0) {
                                    $pName = substr($p, 1);
                                    if (is_numeric($pName) === TRUE && isset($matches[$pName]) === TRUE) {
                                        $vResult[$k1] =  $matches[$pName];
                                    }
                                }
                            }
                            $v['PARAMS'] = $vResult;
                        }
                        $result = array_merge($result, $v);
                        break;
                    }
                }
            }
            if (empty($v) === TRUE && function_exists('landingPageRouter') === TRUE) {
                $result = array_merge($result, landingPageRouter($urlString, $isUseRouter));
            }
        }
        /**
         * alternate router: Zend style
         */
        if ($isUseRouter === FALSE) {
            $isFirstCaseRun = FALSE;
            if (empty($router[0]) === FALSE) {
                if (preg_match("/^[A-Za-z0-9-]+$/", $router[0]) && (is_dir(BASE_PATH . '/Modules/' . $router[0]) || is_dir(BASE_PATH . '/Modules/' . $result['MODULE_NAME']))) {
                    /**
                     * support index controller SEO
                     */
                    $rIndex = 0;
                    if (is_dir(BASE_PATH . '/Modules/' . $router[0])) {
                        $result['MODULE_NAME'] = $router[0];
                        $p1 += 1;
                        $p2 += 1;
                        $fv = 0;
                        $rIndex += 1;
                        $isFirstCaseRun = TRUE;
                    }
                    $isCase1Run = FALSE;
                    $tmpName = NULL;
                    if (empty($router[$rIndex]) === FALSE) {
                        $tmpName = BASE_PATH . '/Modules/' . $result['MODULE_NAME'] . '/Controllers/' . Inc_Utility::processModuleName($router[$rIndex]) . 'Controller' . '.php';
                        if (empty($router[$rIndex + 1]) === FALSE) {
                            if (is_file($tmpName)) {
                                /**
                                 * remove queryString bug
                                 */
                                $tmpRouter = $router;
                                $endRouter = end($tmpRouter);
                                if (empty($endRouter) === TRUE) {
                                    unset($tmpRouter[count($tmpRouter) - 1]);
                                }
                                if (count($tmpRouter) % 2 === $rIndex) {
                                    $isCase1Run = TRUE;
                                    $isFirstCaseRun = TRUE;
                                }
                            }
                        }
                    }
                    if ($isCase1Run === FALSE) {
                        if (is_file($tmpName) === TRUE) {
                            $tmpRouter = array();
                            foreach ($router as $k => $item) {
                                if ($k === $rIndex) {
                                    $tmpRouter[] = $item;
                                    $tmpRouter[] = $result['ACTION_NAME'];
                                } else {
                                    $tmpRouter[] = $item;
                                }
                            }
                            $router = $tmpRouter;
                            $isFirstCaseRun = TRUE;
                        } else {
                            $tmpRouter = array();
                            foreach ($router as $k => $item) {
                                if ($k === $rIndex) {
                                    $tmpRouter[] = $result['CONTROLLER_NAME'];
                                    $tmpRouter[] = $item;
                                } else {
                                    $tmpRouter[] = $item;
                                }
                            }
                            $router = $tmpRouter;
                            $isFirstCaseRun = TRUE;
                        }
                    }
                }
            }
        }
        /**
         * use module default
         */
        if ($isUseRouter === FALSE) {
            if (empty($router[$p1]) === FALSE) { $result['CONTROLLER_NAME'] = Inc_Utility::processModuleName($router[$p1]); }
            if (empty($router[$p2]) === FALSE) { $result['ACTION_NAME'] = Inc_Utility::processModuleName($router[$p2]); }
            $p = $p2 + 1;
            while(empty($router[$p]) === FALSE) {
                if ($p % 2 !== $fv) {
                    $key = $router[$p];
                    $router[$p] = NULL;
                }
                $result['PARAMS'][$key] = $router[$p];
                $p++;
            }
        }
    }
    return $result;
}
$GLOBALS = array_merge($GLOBALS, processUri(@Inc_String::trimQueryString($_SERVER['REDIRECT_URL'])));
/**
 * setup all functions
 * $view only using when load controller and want to pass all view properties from controller Source to controller Dest
 */
function loadController ($moduleName, $controllerName, $actionName, $view = NULL) {
    $uppercaseModuleName = ucwords($moduleName);
    $controllerName = ucwords($controllerName) . "Controller";
    $actionName = lcfirst($actionName . 'Action');
    $cPath = BASE_PATH . '/Modules/' . $moduleName . '/Controllers/' . $controllerName . ".php";
    if (is_file($cPath) === TRUE) {
        /**
         * load custom module: i.e block module
         */
        require_once BASE_PATH . '/Modules/' . $moduleName . '/' . $uppercaseModuleName . "Controller.php";
        require_once $cPath;
        $arguments = array_reverse(func_get_args());
        $leng = count(func_get_args()) - 4;
        $controllerName = $uppercaseModuleName . '_' . $controllerName;
        $controller = new $controllerName();
        if ($view instanceof View) {
            $class_vars2 = get_object_vars($controller->view); // get new view
            $class_vars1 = get_object_vars($view); // get old view
            $controller->view = clone $view;
            foreach ($class_vars2 as $name => $value) {
                $controller->view->$name = $value; // merge new view to old view
            }
        }
        if ($leng > 0) {
            foreach ($arguments as $k => $item) {
                if ($k < $leng && isset($item) === TRUE) {
                    $arg = array();
                    foreach ($item as $ar) {
                        $arg[] = $ar;
                    }
                    call_user_func_array(array($controller, $actionName), array_reverse($arg));
                    break;
                }
            }
        } else {
            $controller->$actionName();
        }
    } else {
        Inc_Utility::throwError('404', $cPath . ' not availlable');
    }
}
if (function_exists('getScript') === FALSE) {
    function getScript ($script) { return BASE_FRONT . $script . '?version=' . VERSION; }
}
function getLibScript ($script) { return LIB_URL . $script . '?version=' . VERSION; }
function generateCallTrace ($e) {
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = $trace;
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    for ($i = 0; $i < $length; $i++) {
        // replace '#someNum' with '$i)', set the right ordering
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' '));
    }
    return implode("\n", $result);
}
function generateTrace ($e) {
    $arrTrace = $e->getTrace();
    array_shift($arrTrace); // remove call to this method
    foreach ($arrTrace as $k => $v) {
        if ($k > 3) {
            echo "...\n";
            break;
        }
        if ($k > 0) {
        echo "------------------------\n";
        }
        echo ($k + 1)  . ")\n";
        var_dump($v);
    }
}
function debug ($var, $traceDetail = FALSE) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    // var_dump the variable into a buffer and keep the output
    ob_start();
    $e = new Exception();
    if ($traceDetail === TRUE) {
        echo "------------------------ Variable ------------------------\n";
    }
    var_dump($var);
    if ($traceDetail === TRUE) {
        echo "------------------------ Trace String ------------------------\n";
        echo generateCallTrace($e) . "\n";
        echo "------------------------ Trace Detail ------------------------\n";
        generateTrace($e) . "\n";
    }
    $output = ob_get_clean();
    // neaten the newlines and indents
    $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
    if(!extension_loaded('xdebug')) {
        $flags = ENT_QUOTES;
        // PHP 5.4.0+
        if (defined('ENT_SUBSTITUTE')) {
            $flags = ENT_QUOTES | ENT_SUBSTITUTE;
        }
        $output = htmlspecialchars($output, $flags);
    }
    $output = '<pre>'
            . $output
            . '</pre>';
    echo $output;
}
function console_log ($var, $traceDetail = FALSE) {
    if (USING_DEBUGBAR === TRUE) {
        global $debugbar;
        $debugbar['messages']->debug($var);
    }
}
if (function_exists('lcfirst') === FALSE) {
    function lcfirst ($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
