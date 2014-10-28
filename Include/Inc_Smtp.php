<?php
class Inc_Smtp extends PHPMailer {
    function config ($config = array()) {
        // End defined
        $this->CharSet = 'UTF-8';
        $this->IsSMTP(); // telling the class to use SMTP
        $this->SMTPDebug  = 0; // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $this->SMTPAuth   = TRUE;       // enable SMTP authentication
        $this->SMTPSecure = strval($GLOBALS['FILE_XML_CONFIG']->smtp_ssl);   // sets the prefix to the servier
        $this->Host       = strval($GLOBALS['FILE_XML_CONFIG']->smtp_server);  // sets GMAIL as the SMTP server
        $this->Port       = strval($GLOBALS['FILE_XML_CONFIG']->smtp_port);  // set the SMTP port for the GMAIL server
        $this->Username   = strval($GLOBALS['FILE_XML_CONFIG']->smtp_email);  // GMAIL username
        $this->Password   = strval($GLOBALS['FILE_XML_CONFIG']->smtp_password);  // GMAIL password
        $this->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        if (empty($config['sendFrom']) === FALSE) {
            $sendFrom = $config['sendFrom'];
        }
        if (empty($config['nameFrom']) === FALSE) {
            $nameFrom = $config['nameFrom'];
        }
        if (empty($config['sendTo']) === FALSE) {
            $sendTo = $config['sendTo'];
        }
        if (empty($config['nameTo']) === FALSE) {
            $nameTo = $config['nameTo'];
        }
        if (empty($config['subject']) === FALSE) {
            $subject = $config['subject'];
        }
        if (empty($config['htmlBody']) === FALSE) {
            $htmlBody = $config['htmlBody'];
        }
        $this->SetFrom($sendFrom, $nameFrom);
        $this->AddReplyTo($sendFrom, $nameFrom);
        $this->Subject = $subject;
        $this->MsgHTML($htmlBody);
        $this->AddAddress($sendTo, $nameTo);
    }
}