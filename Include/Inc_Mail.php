<?php
class Inc_Mail {
    protected $parts;
    protected $to;
    protected $from;
    protected $headers;
    protected $subject;
    protected $body;
    protected $charset;
    protected $MAX_SIZE_FILE;
    protected $type_file;
    function __construct() {
        $this->parts          = array();
        $this->to             =  "";
        $this->replyTo        = "";
        $this->from           =  "";
        $this->subject        =  "";
        $this->body           =  "";
        $this->headers        =  "";
        $this->MAX_SIZE_FILE  =  200000;
        $this->type_file = array(
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'pdf'  => 'application/pdf',
            'zip'  => 'application/x-zip-compressed',
            'rar'  => 'application/x-rar-compressed'
        );
    }
    /**
     * Add an attachment to the mail object
     *
     * @param string $message
     * @param string $name
     * @param string $ctype
     */
    public function add_attachment($message, $name =  "", $ctype = "application/octet-stream") {
        if (array_key_exists($ctype, $this->type_file)) {
            $ctype = $this->type_file[$ctype];
        }
        $this->parts[]  = array (
            "ctype"     => $ctype,
            "message"   => $message,
            "encode"    => NULL,
            "name"      => $name
        );
    }
    /**
     * Build message parts of an multipart mail
     *
     * @param array $part
     * @return message
     */
     private function build_message($part) {
        $message  = $part[ "message"];
        $message  = chunk_split(base64_encode($message));
        $encoding =  "base64";
        $result = "Content-Type: ".$part[ "ctype"]. (($part[ "name"]) ? ("; name = \"" . $part[ "name"] . "\"") :  "") . "\nContent-Transfer-Encoding: $encoding\n";
        if ($part[ "name"]) { // if file => add content dispotion
            $result .= "Content-Disposition: attachment\n";
        }
        $result .= "\n$message\n";
        return $result;
     }
    /**
     * Build a multipart mail
     *
     * @return Build a multipart mail
     */
    private function build_multipart() {
        $boundary  =  "b" . md5(uniqid(time()));
        $multipart = "Content-Type: multipart/mixed; boundary = $boundary\n\nThis is a MIME encoded message.\n\n--$boundary";
        for ($i = sizeof($this->parts)-1; $i >= 0; $i--) {
            $multipart .=  "\n".$this->build_message($this->parts[$i])."--$boundary";
        }
        return $multipart.=  "--\n";
    }
    /**
     * returns the constructed mail
     *
     * @param bool $complete
     * @return the constructed mail
     */
    private function get_mail($complete = true) {
        $mime =  "";
        if (!empty($this->from))
            $mime .=  "From: ".$this->from. "\r\n";
        if(!empty($this->replyTo))
            $mime .= "Reply-To: ".$this->replyTo."\r\n";
        if (!empty($this->headers))
            $mime .= $this->headers. "\r\n";
        if ($complete) {
            if (!empty($this->to)) {
                $mime .= "To: $this->to\r\n";
            }
            if (!empty($this->subject)) {
                $mime .= "Subject: $this->subject\n";
            }
        }
        if (!empty($this->body)) {
            // #2191 - Thoa added 2006-Oct-12
            if(($this->char)=='iso') {
                $this->char = "iso-8859-1";
                $this->body = $this->numeric_entity_utf8($this->body);
            } else {
                $this->char = "utf-8";
            }
            $this->add_attachment($this->body,  "",  "text/html; charset={$this->char}");
         }
        $mime .=  "MIME-Version: 1.0\n".$this->build_multipart();
        return $mime;
    }
    private function numeric_entity_utf8 ($utf8_string) {
        $out = "";
        $ns  = strlen ($utf8_string);
        for ($nn = 0; $nn < $ns; $nn++) {
            $ch = $utf8_string [$nn];
            $ii = ord ($ch);
            if ($ii < 128) {
                $out .= $ch;
            } else if ($ii >>5 == 6) {
                 $b1 = ($ii & 31);
                 $nn++;
                 $ch = $utf8_string [$nn];
                 $ii = ord ($ch);
                 $b2 = ($ii & 63);
                 $ii = ($b1 * 64) + $b2;
                 $ent = sprintf ("&#%d;", $ii);
                 $out .= $ent;
            } else if ($ii >>4 == 14) {
                    $b1 = ($ii & 31);
                    $nn++;
                    $ch = $utf8_string [$nn];
                    $ii = ord ($ch);
                    $b2 = ($ii & 63);
                    $nn++;
                    $ch = $utf8_string [$nn];
                    $ii = ord ($ch);
                    $b3 = ($ii & 63);
                    $ii = ((($b1 * 64) + $b2) * 64) + $b3;
                    $ent = sprintf ("&#%d;", $ii);
                    $out .= $ent;
            } else if ($ii >>3 == 30) {
                $b1 = ($ii & 31);
                $nn++;
                $ch = $utf8_string [$nn];
                $ii = ord ($ch);
                $b2 = ($ii & 63);
                $nn++;
                $ch = $utf8_string [$nn];
                $ii = ord ($ch);
                $b3 = ($ii & 63);
                $nn++;
                $ch = $utf8_string [$nn];
                $ii = ord ($ch);
                $b4 = ($ii & 63);
                $ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
                $ent = sprintf ("&#%d;", $ii);
                $out .= $ent;
            }
        }
        return $out;
    }
    /**
     * Send the mail (last class-function to be called)
     *
     * @return true: success, false: fail
     */
    public function send() {
        $mime = $this->get_mail(false);
        $this->subject = $this->numeric_entity_utf8($this->subject);
        if (mail($this->to, $this->subject,  "", $mime)) {
            return true;
        } else {
            return false;
        }
    }
    /**
    * allow send email with attached file that has been created on server.
    *
    * @param string $from
    * @param string $to
    * @param string $subject
    * @param string $body
    * @param array  $attachedFile
    * @param int $MAX_SIZE_FILE
    * @return true: success, false: fail
    */
    public function sendAttachFile ($from, $to, $subject, $body, $files, $MAX_SIZE_FILE = 0, $charset='utf-8') {
        //prepair send and attach file
        $this->from    = $from;
        $this->to      = $to;
        $this->subject = $subject;
        $this->body    = $body;
        $this->char    = $charset;
        if (empty($this->replyTo) === TRUE) {
            $this->replyTo = $from;
        }
        if ($MAX_SIZE_FILE) {
            $this->MAX_SIZE_FILE = $MAX_SIZE_FILE;
        }
        foreach ($files as $attachedFile) {
            $has_upload   = true;
            $tempFilename = $attachedFile['tmp_name'];
            $fileType     = $attachedFile['type'];
            $filename     = $attachedFile['name'];
            $filesize     = $attachedFile['size'];
            $content_type = $fileType;
            if ($has_upload) {
                $data = file_get_contents($tempFilename);
                if ($filename <> "" && $this->type_file[$fileType] && ($filesize <= $this->MAX_SIZE_FILE)) {
                    # append the attachment
                    $this->add_attachment($data, $filename, $content_type);
                } else {
                    return false;
                }
            }
        }
        if (count($this->parts)) {
            return $this->send();
        } else {
            if ($charset=='iso') {
                $charset = "iso-8859-1";
                $body    = $this->numeric_entity_utf8($body);
            } else {
                $charset = "utf-8";
            }
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset={$charset}\r\n";
            $headers .= "From: $from\r\n";
            $this->subject = $this->numeric_entity_utf8($this->subject);
            if ($cc!='') $headers .= "Cc: $cc\r\n";
            if ($bcc!='') $headers .= "Bcc: $bcc\r\n";
            if (mail($to, $this->subject, $body, $headers, "-f $from")) return true;
            return false;
        }
    } //END sendAttachFile
    /**
    * allow send email with attached file that has been created on server.
    *
    * @param string $from
    * @param string $to
    * @param string $subject
    * @param string $body
    * @param array  $attachedFile
    * @param int $MAX_SIZE_FILE
    * @return true: success, false: fail
    */
    public function sendMailHtml ($from, $to, $subject, $body, $charset='utf-8', $contentType = 'text/html') {
        //prepair send and attach file
        $this->from    = $from;
        $this->to      = $to;
        $this->subject = $subject;
        $this->body    = $body;
        $this->char    = $charset;
        if (empty($this->replyTo) === TRUE) {
            $this->replyTo = $from;
        }
        if ($charset=='iso') {
            $charset = "iso-8859-1";
            $body    = $this->numeric_entity_utf8($body);
        } else {
            $charset = "utf-8";
        }
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "Return-Path: $from\r\n";
        $headers .= "From: $from\r\n";
        if ($contentType === 'text/plain') {
            $headers .= "Content-type: text/plain; charset={$charset}\r\n";
        } else {
            $headers .= "Content-type: text/html; charset={$charset}\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP". phpversion() ."\r\n";
        }
        $this->subject = $this->numeric_entity_utf8($this->subject);
        if (empty($cc) === FALSE) $headers .= "Cc: $cc\r\n";
        if (empty($bcc) === FALSE) $headers .= "Bcc: $bcc\r\n";
        //echo ($headers);
        //exit();
        if (mail($to, $this->subject, $body, $headers, "-f $from")) return true;
        return false;
    }
    public function sendMailText ($from, $to, $subject, $body, $charset='utf-8') {
        return $this->sendMailHtml($from, $to, $subject, $body, $charset, 'text/plain');
    }
    static function validateEmailExist ($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE) {
            if (checkdnsrr(array_pop(explode('@', $email)), 'MX')) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
/*
// 0) prepare data
$from = 'songpham@cfyc.com.vn';
$to = 'song20002005@yahoo.com';
$mail = new Inc_Mail();
// 1) start send email no attachment and html
$subject = 'Test email html';
$body = '<html>
<head>
  <title>Birthday Reminders for August</title>
</head>
<body>
  <p>Here are the birthdays upcoming in August!</p>
  <table>
    <tr>
      <th>Person</th><th>Day</th><th>Month</th><th>Year</th>
    </tr>
    <tr>
      <td>Joe</td><td>3rd</td><td>August</td><td>1970</td>
    </tr>
    <tr>
      <td>Sally</td><td>17th</td><td>August</td><td>1973</td>
    </tr>
  </table>
</body>
</html>';
$mail->sendMailHtml($from, $to, $subject, $body);
// 2) start send email no attachment and no html
$subject = 'Test email text plain';
$body = 'text plain';
$mail->sendMailText($from, $to, $subject, $body);
// 3) start send email 1 attachment
$attachedFile = array(
    0 => array(
        'tmp_name' => 'thanchu.docx',
        'type'     => 'docx',
        'name'     => 'Than Chu.docx',
        'size'     => '11'
    )
);
$subject = 'Test email 1 attach';
$body = '<p>Test email 1 attach body</p>';
$mail->sendAttachFile($from, $to, $subject, $body, $attachedFile);
// 4) start send email multiple attachment
$attachedFile = array(
    0 => array(
        'tmp_name' => 'thanchu.docx',
        'type'     => 'docx',
        'name'     => 'Than Chu.docx',
        'size'     => '11'
    ),
    1 => array(
        'tmp_name' => 'sono.docx',
        'type'     => 'docx',
        'name'     => 'so no.docx',
        'size'     => '11'
    )
);
$subject = 'Test email multiple attach';
$body = '<p>Test email multiple attach body</p>';
$mail->sendAttachFile($from, $to, $subject, $body, $attachedFile);
*/
