<?php
class View {
    function loadView ($fileName) {
        $layoutTemplate = BASE_PATH . '/Layouts/' . $GLOBALS['MODULE_NAME'] . '.phtml';
        $contentFile = BASE_PATH . '/Modules/' . $GLOBALS['MODULE_NAME'] . '/' . $fileName . '.phtml';
        if (is_file($layoutTemplate)) {
            require_once $layoutTemplate;
        } else {
            require_once $contentFile;
        }
    }
    function loadBlockView ($fileName) {
        require BASE_PATH . '/Modules/' . BLOCK_MODULE . '/' . $fileName . '.phtml';
    }
    function loadViewTemplate ($fileName) {
        /* we are going to use full path if it is */
        $filePath = $fileName . '.phtml';
        if (is_file($filePath) === FALSE) {
            $filePath = BASE_PATH . '/Modules/' . $GLOBALS['MODULE_NAME'] . '/' . $fileName . '.phtml';
        }
        require $filePath;
    }
    function showBlock ($controllerName, $actionName) {
        $controllerName = Inc_Utility::processModuleName($controllerName);
        $actionName = Inc_Utility::processModuleName($actionName);
        /**
         * support pass arguments: $this->showBlock('modal', 'class-detail', 2, 'song');
         * output: array(0 => song, 1 => 2)
         */
        $arguments = array_reverse(func_get_args());
        $leng = count(func_get_args()) - 2;
        if ($leng > 0) {
            foreach ($arguments as $k => $item) {
                if ($k < $leng && isset($item) === TRUE) {
                    $arg = array();
                    $value = NULL;
                    for ($i = $k; $i < $leng; $i++) {
                        $arg[] = $arguments[$i];
                    }
                    break;
                }
            }
        }
        loadController(BLOCK_MODULE, $controllerName, $actionName, $this, $arg);
    }
}
