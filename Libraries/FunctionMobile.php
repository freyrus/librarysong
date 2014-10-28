<?php
spl_autoload_register(function ($name) {
    if (strpos($name, 'Models') === 0) {
        require_once '../Class/' . $name . ".php";
    } else if (strpos($name, '\\') === FALSE) {
        $arr = explode("_", $name);
        if (isset($arr[2])) {
            require_once "../$arr[0]/$arr[1]/models/$name.php";
        }
    }
});