<?php
/**
 * support password_compat
 * create: $hash = password_hash(md5($password), PASSWORD_BCRYPT);
 * use: password_verify($password, $hash)
 */
require_once LIB_PATH . "/Plugins/password_compat/password.php";
class Inc_PasswordHash {
    public static function passwordHash ($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    public static function passwordVerify ($password, $hash) {
        return password_verify($password, $hash);
    }
}