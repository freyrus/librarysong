<?php
class Controller {
    function __construct () {
        $this->view = new View();
        $this->view->_errors = array();
        $this->view->loadJs = array();
        $this->view->loadCss = array();
        /**
         * clear cache by: header('Location: ' . $_SERVER['HTTP_REFERER']); Not use window.history.back()
         */
        $expire = 60 * 60;// seconds, minutes, hours, days
        header('Cache-Control: public, maxage=' . $expire);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        /* This has the effect of forcing the browser to treat content in accordance with the Content-Type header */
        header("X-Content-Type-Options: nosniff");
        /* The HTTP Strict-Transport-Security (HSTS) header instructs the browser to (for a given time) only use https */
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        /* Means that script files may only come from the current domain or from apis.google.com (the Google JavaScript CDN). */
        /* header("Content-Security-Policy: script-src 'self' https://apis.google.com"); */
        /**
         * DebugBar
         */
        if (USING_DEBUGBAR === TRUE) {
            global $debugbarRenderer;
            $this->view->debugbarRenderer = $debugbarRenderer;
        }
    }
    function countAccess () {
        $file = BASE_FRONT_PATH . '/countaccess.txt';
        if (file_exists($file)) {
            $current = trim(file_get_contents($file));
            if (is_numeric($current) === TRUE) {
                $current = intval($current);
                if (isset($_SESSION['countAccess']) === FALSE) {
                    file_put_contents($file, ++$current);
                    $_SESSION['countAccess'] = TRUE;
                }
            }
        }
        return $current;
    }
    function __destruct () { }
}
