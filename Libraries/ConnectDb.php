<?php
class ConnectDb {
    protected $result = NULL;
    function connectDb () {
        //uses base64 to get sure the string is escaped
        $base64 = base64_encode(defined('PASSWORD_HOST') === TRUE ? PASSWORD_HOST : 'ifrc');
        $function = "return base64_decode('" . $base64 .  "');";
        $pass_host = create_function("", $function);
        try {
            return new PDO(
                "mysql:host=" . HOST . ";port=" . PORT . ";dbname=" . DBNAME,
                USER_HOST,
                call_user_func($pass_host),
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }
    function connect () {
        global $conn;
        if (empty($conn) === TRUE) {
            $conn = $this->connectDb();
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (USING_DEBUGBAR === TRUE) {
                require_once BASE_PATH . '/Debugbar/connection.php';
            }
        }
    }
    function disconnect () {
        global $conn;
        $conn = NULL;
    }
    function reconnect () {
        $this->disconnect();
        $this->connect();
    }
    function beginTransaction () {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        $conn->beginTransaction();
    }
    function commit () {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        $conn->commit();
    }
    function rollBack () {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        $conn->rollBack();
    }
    function query ($sql, $execute = TRUE) {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        if ($execute === TRUE) {
            $this->executeQuery($sql);
        } else {
            $conn->query($sql);
        }
    }
    function executeQuery ($sql) {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        $this->result = $conn->prepare($sql);
        $this->result->execute();
    }
    function fetch () {
        $row = null;
        if ($this->result) {
            $row = $this->result->fetch();
        }
        return $row;
    }
    function count () {
        if ($this->result) {
            return $this->result->rowCount();
        }
        return 0;
    }
    function numFields () {
        if ($this->result) {
            return $this->result->columnCount();
        }
        return 0;
    }
    function columnMeta ($i) {
        if ($this->result) {
            return $this->result->getColumnMeta($i);
        }
        return NULL;
    }
    function free () {
        if ($this->result) {
            $this->result->closeCursor();
        }
    }
    function lastInsertId ($table) {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        return $conn->lastInsertId($table);
    }
    function foundRow () {
        global $conn;
        if (empty($conn) === TRUE) {
            $this->reconnect();
        }
        return $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    }
    function displayPDOException ($e) {
        echo '<table>';
        echo '<tr><td width="60">ERROR</td><td>: <strong>' . $e->getMessage() . '</strong></td></tr>';
        echo '<tr><td>File</td><td>: <strong>' . $e->getFile() . '</strong></td></tr>';
        echo '<tr><td>Line</td><td>: <strong>' . $e->getLine() . '</strong></td></tr>';
        echo '<tr><td colspan="2">';
        $arrError = $e->getTrace();
        foreach ($arrError as $k => $v) {
            if ($k > 3) {
                break;
            }
            debug($v);
        }
        echo '<td></tr>';
        echo '<table>';
    }
}
