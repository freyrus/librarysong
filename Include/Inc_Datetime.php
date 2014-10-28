<?php
class Inc_Datetime {
    static function validateMysqlDate ($date) { return strtotime($date); }
    /**
     * input: '2012/10/30 ...', 'vn' | 'en'
     * output: '30/10/2012' | '10/30/2012'
     */
    static function formatDate ($dateTime, $lang = 'vn') {
        $result = NULL;
        if (Inc_Datetime::validateMysqlDate($dateTime) !== FALSE) {
            $kq    = array();
            $day   = substr($dateTime, 8 ,2);
            $month = substr($dateTime, 5, 2);
            $year  = substr($dateTime, 0, 4);
            if ($lang == 'vn') {
                if ($day)   $kq[] = $day;
                if ($month) $kq[] = $month;
            } else {
                if ($month) $kq[] = $month;
                if ($day)   $kq[] = $day;
            }
            if ($year)  $kq[] = $year;
            $result = implode('-', $kq);
        }
        return $result;
    }
    /**
     * input: '2012/10/30 ...', 'vn' | 'en'
     * output: '30/10/2012 10:30:01'
     */
    static function formatDateTime ($dateTime, $lang = 'vn') {
        $result = NULL;
        if (Inc_Datetime::validateMysqlDate($dateTime) !== FALSE) {
            $kq    = array();
            $day   = substr($dateTime, 8 ,2);
            $month = substr($dateTime, 5, 2);
            $year  = substr($dateTime, 0, 4);
            $hour   = substr($dateTime, 11 ,2);
            $minute = substr($dateTime, 14, 2);
            $second  = substr($dateTime, 17, 2);
            if ($lang == 'vn') {
                if ($day)   $kq[] = $day;
                if ($month) $kq[] = $month;
            } else {
                if ($day)   $kq[] = $day;
                if ($month) $kq[] = $month;
            }
            if ($year)  $kq[] = $year;
            $result = implode('-', $kq);
            $kq = array();
            if ($hour)    $kq[] = $hour;
            if ($minute)  $kq[] = $minute;
            if ($second)  $kq[] = $second;
            $result .= ' ' . implode(':', $kq);
        }
        return $result;
    }
    /**
     * input: '2012/10/30 ...', 'vn' | 'en'
     * output: 'Thứ 2' | 'Mon'
     */
    static function formatDay ($dateTime, $lang = 'vn') {
        $result = NULL;
        if (Inc_Datetime::validateMysqlDate($dateTime) !== FALSE) {
            if ($lang == 'vn') {
                $result = date("N", strtotime($dateTime));
                switch ($result) {
                    case 1: $result = 'Thứ hai'; break;
                    case 2: $result = 'Thứ ba'; break;
                    case 3: $result = 'Thứ tư'; break;
                    case 4: $result = 'Thứ năm'; break;
                    case 5: $result = 'Thứ sáu'; break;
                    case 6: $result = 'Thứ bảy'; break;
                    case 7: $result = 'Chủ nhật'; break;
                }
            } else {
                $result = date("l", strtotime($dateTime));
            }
        }
        return $result;
    }
    /**
     * input: '30-10-2013'
     * output: '2013-10-30'
     */
    static function reverseDate ($dateTime) {
        $result = NULL;
        if (Inc_Datetime::validateMysqlDate($dateTime) !== FALSE) {
            $dateTime = explode('-', $dateTime);
            $dateTime = array_reverse($dateTime);
            $result   = implode('-', $dateTime);
        }
        return $result;
    }
    /**
     * input: '1988-10-10 10:10:10'
     * output: '1988' || '10' ...
     */
    static function getDataFromDateTime ($dateTime, $data = 'Y') {
        $result = NULL;
        if (Inc_Datetime::validateMysqlDate($dateTime) !== FALSE) {
            $result = date($data, strtotime($dateTime));
        }
        return $result;
    }
    /**
     * input: '1988-10-10 10:10:10'
     * output: 26
     */
    static function getYearOldFromDateTime ($dateTime) {
        $yearOfBirth = Inc_Datetime::getDataFromDateTime($dateTime, 'Y');
        if (empty($yearOfBirth) === FALSE) {
            return date("Y") - $yearOfBirth;
        }
        return 0;
    }
}
