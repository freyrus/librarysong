<?php
/**
 * Using:
 * $test = new ConnectFtp();
 * $test->getCurrentDirectory();
 * $test->move('10.jpg', '10.jpg');
 */
class ConnectFtp {
    private $_resource = NULL;
    function __construct () {
        $this->_resource = ftp_connect(FTP_SERVER);
        if ($this->_resource === FALSE) {
            Inc_Utility::throwError('500', 'cannot access ftp server');
            return FALSE;
        }
        return ftp_login($this->_resource, FTP_USERNAME, FTP_PASSWORD);
    }
    function __destruct () {
        ftp_close($this->_resource);
    }
    function makeDir ($dirName) {
        return ftp_mkdir($this->_resource, FTP_PRE_FOLDER . $dirName);
    }
    function removeDir ($dirName) {
        return ftp_rmdir($this->_resource, FTP_PRE_FOLDER . $dirName);
    }
    function delete ($filePath) {
        return ftp_delete($this->_resource, FTP_PRE_FOLDER . $filePath);
    }
    function size ($filePath) {
        return ftp_size($this->_resource, FTP_PRE_FOLDER . $filePath);
    }
    /**
     * FTP_ASCII: text file
     * FTP_BINARY: image file
     */
    function upload ($localPath, $remotePath, $mode = FTP_BINARY) {
        return ftp_put($this->_resource, FTP_PRE_FOLDER . $remotePath, $localPath, $mode);
    }
    function move ($localPath, $remotePath, $mode = FTP_BINARY) {
        $result = $this->upload($localPath, $remotePath, $mode);
        if ($result === TRUE) {
            unlink($localPath);
        }
        return $result;
    }
    function isDirExists ($folder) {
        $isExist = TRUE;
        $currentFolder = ftp_pwd($this->_resource);
        $isExist = @ftp_chdir($this->_resource, FTP_PRE_FOLDER . $folder);
        ftp_chdir($this->_resource, $currentFolder);
        return $isExist;
    }
    function isFileExists ($file) {
        if (ftp_size($this->_resource, FTP_PRE_FOLDER . $file) > -1) {
            return TRUE;
        }
        return FALSE;
    }
    function getCurrentDirectory () {
        return ftp_pwd($this->_resource);
    }
    function listFileFromCurrentDirectory ($dir = ".") {
        $arr = ftp_nlist($this->_resource, FTP_PRE_FOLDER . $dir);
        $result = array();
        if (empty($arr) === FALSE) {
            foreach ($arr as $item) {
                if (in_array($item, array('.', '..')) === FALSE) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }
}
