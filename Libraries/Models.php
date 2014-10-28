<?php
class Models extends ConnectDb {
    function __construct () {
        $this->connect();
        $this->dbGenerateSql = FALSE;
        $this->dbReturnSql = FALSE;
        $this->dbDefaultOrder = '_id';
        $this->field = array();
        $this->loadModel();
    }
    function __destruct () {}
    function loadModel ($tableName = '') {
        $field = array();
        if (empty($tableName) === TRUE) {
            if (isset($this->tableName) === TRUE) {
                require BASE_PATH . '/Tables/' . $this->tableName . '.php';
            }
        } else {
            require BASE_PATH . '/Tables/' . $tableName . '.php';
        }
        $this->field = array_merge($this->field, $field);
    }
    function __get ($name) {
        if (array_key_exists($name, $this->field)) {
            return $this->field[$name];
        }
        return NULL;
    }
    function toArray () {
        $result = $this->result->fetchAll(PDO::FETCH_ASSOC);
        $this->free();
        return $result;
    }
    function toRow () {
        $row = $this->result->fetch(PDO::FETCH_ASSOC);
        $this->free();
        return empty($row) === FALSE ? $row : array();
    }
    function toOne () {
        $row = $this->toRow();
        if (empty($row) === FALSE) {
            return reset($row);
        }
        return NULL;
    }
    function queryOne ($sql) {
        $this->query($sql);
        $row = $this->toRow();
        if (empty($row) === FALSE) {
            return reset($row);
        }
        return NULL;
    }
    function queryRow ($sql) {
        $this->query($sql);
        return $this->toRow();
    }
    function queryAll ($sql) {
        $this->query($sql);
        return $this->toArray();
    }
    function toMongoRow () {
        $row = $this->toRow();
        unset($row['_id']);
        return $row;
    }
    function find ($table, $where = '', $order = '', $limit = '', $select = '*', $groupby = '', $having = '') {
        if (empty($where) === FALSE) {
            if (is_array($where)) {
                $sql = array();
                foreach ($where as $k => $v) {
                    preg_match('/^[A-Za-z0-9_]+/', $k, $matches);
                    if (is_null($v) === TRUE) {
                        preg_match('/^[A-Za-z0-9_]+/', $k, $matches);
                        if (empty($matches[0]) === FALSE) {
                            $str = "$matches[0] IS NULL";
                        }
                    } else {
                        $str = "{$k} '{$v}'";
                    }
                    $sql[] = $str;
                }
                $where = implode(" and ", $sql);
                $where = "where $where";
            } else {
                $where = "where $where";
            }
        } else {
            $where = NULL;
        }
        if ($order != "") {
            $order = "order by $order";
        }
        if ($limit != "") {
            $limit = "limit $limit";
        }
        if ($select != "*" && is_array($select) === TRUE) {
            $select = implode(",", $select);
        }
        if ($groupby != "") {
            $groupby = "group by $groupby";
        }
        if ($having != "") {
            $having = "having $having";
        }
        $sql = "select $select from $table $where $groupby $having $order $limit";
        if ($this->dbGenerateSql === TRUE) {
            echo $sql . '<br/><br/>';
        }
        if ($this->dbReturnSql === TRUE) {
            return $sql;
        }
        $this->query($sql);
    }
    function insert ($table, $arr, $delayed = '') {
        $str = implode(", ", array_keys($arr));
        $arr2 = array_values($arr);
        foreach ($arr2 as $item) {
            if ($item === NULL) {
                $arr3[] = "NULL";
            } else {
                if (is_int($item)) {
                    $arr3[] = $item;
                } else {
                    $arr3[] = "'" . $item . "'";
                }
            }
        }
        $str2 = implode(", ", $arr3);
        $sql = "insert " . $delayed . " into $table($str) values($str2)";
        if ($this->isGenerateSql === TRUE) {
            echo $sql . '<br/><br/>';
        }
        if ($this->dbReturnSql === TRUE) {
            return $sql;
        }
        $this->query($sql, TRUE);
        return $this->lastInsertId($table);
    }
    /**
     * $cols = array (id_controller, name)
     * $arr = array (
     *     array(1, 'index'),
     *     array(1, 'edit'),
     *     array(1, 'add')
     * )
     */
    function insertMultiples ($table, $cols, $arr, $delayed = '') {
        /**
         * preprocess $arr
         */
        foreach ($arr as &$item) {
            if (is_array($item) === TRUE) { // support array
                foreach ($item as &$t) {
                    if ($t === NULL) { // support NULL value
                        $t = NULL;
                    } else {
                        $t = "'" . $t . "'";
                    }
                }
                unset($t);
                $item = implode(',', $item);
            }
        }
        unset($item);
        $cols = implode(", ", $cols);
        $values = '(' . implode("), (", $arr) . ')';
        $sql = "insert " . $delayed . " into $table($cols) values $values";
        if ($this->isGenerateSql === TRUE) {
            echo $sql . '<br/><br/>';
        }
        if ($this->dbReturnSql === TRUE) {
            return $sql;
        }
        $this->query($sql);
        return $this->lastInsertId($table, TRUE);
    }
    function update ($table, $arr, $where = NULL) {
        if (is_array($arr)) {
            $str = array();
            foreach ($arr as $k => $v) {
                if ($v === NULL) {
                    $str[] = "$k = NULL";
                } else {
                    if (is_int($v)) {
                        $str[] = "$k = $v";
                    } else {
                        $str[] = "$k = '$v'";
                    }
                }
            }
            $str2 = implode(", ", $str);
        } else {
            $str2 = $arr;
        }
        $where = (empty($where) === FALSE) ? "where {$where}" : NULL;
        $sql = "update $table set $str2 {$where}";
        if ($this->dbGenerateSql === TRUE) {
            echo $sql . '<br/><br/>';
        }
        if ($this->dbReturnSql === TRUE) {
            return $sql;
        } else {
            $this->query($sql, TRUE);
        }
    }
    function delete ($table, $where) {
        $sql = "delete from $table where $where";
        if ($this->dbGenerateSql === TRUE) {
            echo $sql . '<br/><br/>';
        }
        if ($this->dbReturnSql === TRUE) {
            return $sql;
        } else {
            $this->query($sql, TRUE);
        }
    }
    /**
     * arguments: $val, array values, $orderBy, $limit, $select, $groupby, $having
     * should pass $table_name if want to use this function
     * can write call like that for multi condition:
     * $mcity->getById_And_Name(array($name1, $name2), 'id desc', '0, 1', array('id'), 'id', 'id > 1');
     */
    public function __call($method, $arguments) {
        $has        = FALSE;
        $val        = (isset($arguments[0]) === TRUE) ? $arguments[0] : NULL;
        $orderBy    = (empty($arguments[1]) === FALSE) ? $arguments[1] : NULL;
        $limit      = (empty($arguments[2]) === FALSE) ? $arguments[2] : NULL;
        $select     = (empty($arguments[3]) === FALSE) ? $arguments[3] : '*';
        $groupby    = (empty($arguments[4]) === FALSE) ? $arguments[4] : NULL;
        $having     = (empty($arguments[5]) === FALSE) ? $arguments[5] : NULL;
        switch (true) {
            case (0 === strpos($method, 'getBy')):
                $by = substr($method, 5);
                $method = 'toArray';
                $has = TRUE;
                break;
            case (0 === strpos($method, 'getOneBy')):
                $by = substr($method, 8);
                $method = 'toRow';
                $has = TRUE;
                break;
            case (0 === strpos($method, 'getAllData')):
                $method = 'toArray';
                $has = TRUE;
                break;
        }
        if ($has === TRUE && empty($this->tableName) === FALSE) {
            if (empty($by) === FALSE) {
                /*
                 * Only support and or or, not support BOTH
                 */
                $arrBy = array();
                if (strpos($by, '_And_') !== FALSE) {
                    $cond = 'and';
                    $arrBy = explode('_And_', $by);
                } else if (strpos($by, 'Or') !== FALSE) {
                    $cond = 'or';
                    $arrBy = explode('_Or_', $by);
                }

                if (count($arrBy) > 0) {
                    $fieldName = array();
                    foreach ($arrBy as $item) {
                        $fieldName[] = Inc_String::classify($item, '_');
                    }
                } else {
                    $fieldName = Inc_String::classify($by, '_');
                }
            }
            if (isset($val) === TRUE || is_null($val) === TRUE) {
                $where = array();
                if (is_array($val) === TRUE && ($cond === 'or' || $cond === 'and')) {
                    foreach ($fieldName as $k => $v) {
                        if ($v === 'id') $v = '_' . $v;
                        $where[$v . ' = '] = $val[$k];
                    }
                } else {
                    if (empty($fieldName) === FALSE) {
                        if ($fieldName === 'id') {
                            $fieldName = '_' . $fieldName;
                        }
                        $where[$fieldName . ' = '] = $val;
                    }
                }
            }
            $this->find($this->tableName, $where, $orderBy, $limit, $select, $groupby, $having);
            $result = $this->$method();
            return $result;
        }
    }
    /**
     * input: $this->find($tableName, $where, $orderBy,$limit, $this->parseSelectFoundRows(array('name', '_id', $getAll)));
     * output: 'SQL_CALC_FOUND_ROWS name, id'
     */
    function parseSelectFoundRows ($select, $getAll = FALSE) {
        $result = $select;
        if ($getAll === TRUE) {
            $result = array('SQL_CALC_FOUND_ROWS ' . implode(', ', $select));
        }
        return $result;
    }
    function getForeignKey ($arrTable = array(), $tableChild = "", $keyParent = "", $keyChild = "", $method = "equal", $preFix = preFix, $diretory = "", $where = array(), $namePrefix = "", $orderBy = "", $limit = "", $select = "*", $isCompareLowerCase = FALSE) {
        // $director: dùng khi có trên 2 level
        // $namePrefix: dùng khi không muốn dùng name mặc định(name mặc định là tableName)
        if(!$namePrefix){
            $namePrefix = $tableChild;
        }
        $sql = array();
        if(strlen($preFix) == 1){
            unset($sql);
            $sql = array();
            if(!empty($where)){
                foreach($where as $k => $v){
                    if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                }
            }
            switch($method){
                case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($arrTable[$keyParent]) : $arrTable[$keyParent]) . "'"; break;
                case 'in' : $sql[] = $keyChild . " in (" . $arrTable[$keyParent] . ")"; break;
                case 'not_in' : $sql[] = $keyChild . " not in (" . $arrTable[$keyParent] . ")"; break;
                case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                case 'regexp' :
                $sql[] = $keyChild . " like binary '%^(" . $arrTable[$keyParent] . ")$%'";
                case 'not_in_regexplower' :
                $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($arrTable[$keyParent])))) . "')"; break;
                case 'regexplower' :
                $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($arrTable[$keyParent]) . ")$%'";
                //$sql[] = "'".$arrTable[$keyParent] . "' regexp " . $keyChild;
                break;
            }
            $sql=@implode(" and ",$sql);
            $this->find($tableChild, $sql, $orderBy, $limit, $select);
            if ($this->count() != 0) {
                $arrTable[$preFix . $namePrefix] = $this->toArray();
                return $arrTable;
            } else {
                return $arrTable;
            }
        }else{
            if(strlen($preFix) == 2){
                $kq = array();
                //debug($arrTable);
                foreach($arrTable as $item){
                    unset($sql);
                    $sql = array();
                    if(!empty($where)){
                        foreach($where as $k => $v){
                            if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                        }
                    }
                    switch($method){
                        case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($item[$keyParent]) : $item[$keyParent]) . "'"; break;
                        case 'in' : $sql[] = $keyChild . " in (" . $item[$keyParent] . ")"; break;
                        case 'not_in' : $sql[] = $keyChild . " not in (" . $item[$keyParent] . ")"; break;
                        case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                        case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                        case 'regexp' :
                        $sql[] = $keyChild . " like binary '%^(" . $item[$keyParent] . ")$%'";
                        case 'not_in_regexplower' :
                        $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($item[$keyParent])))) . "')"; break;
                        case 'regexplower' :
                        $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($item[$keyParent]) . ")$%'";
                        //$sql[] = "'".$item[$keyParent] . "' regexp " . $keyChild;
                        break;
                    }
                    $sql=@implode(" and ",$sql);
                    $this->find($tableChild, $sql, $orderBy, $limit, $select);
                    if ($this->count() != 0) {
                        $item[$preFix . $namePrefix] = $this->toArray();
                        $kq[] = $item;
                    }else{
                        $kq[] = $item;
                    }
                }
                if(!empty($kq)){
                    return $kq;
                }else{
                    return $arrTable;
                }
            }else{
                if(strlen($preFix) == 3){
                    $diretory = explode("|", $diretory);
                    $outPut = $arrTable;
                    $i=0;
                    foreach($arrTable[$diretory[0]] as $items){
                        unset($kq);
                        $j=0;
                        foreach($items[$diretory[1]] as $itemss){
                            unset($sql);
                            $sql = array();
                            if(!empty($where)){
                                foreach($where as $k => $v){
                                    if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                                }
                            }
                            switch($method){
                                case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($itemss[$keyParent]) : $itemss[$keyParent]) . "'"; break;
                                case 'in' : $sql[] = $keyChild . " in (" . $itemss[$keyParent] . ")"; break;
                                case 'not_in' : $sql[] = $keyChild . " not in (" . $itemss[$keyParent] . ")"; break;
                                case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                case 'regexp' :
                                $sql[] = $keyChild . " like binary '%^(" . $itemss[$keyParent] . ")$%'";
                                case 'not_in_regexplower' :
                                $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($itemss[$keyParent])))) . "')"; break;
                                case 'regexplower' :
                                $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($itemss[$keyParent]) . ")$%'";
                                //$sql[] = "'".$itemss[$keyParent] . "' regexp " . $keyChild;
                                break;
                            }
                            $sql=@implode(" and ",$sql);
                            $this->find($tableChild, $sql, $orderBy, $limit, $select);
                            if ($this->count() != 0) {
                                $itemss[$preFix . $namePrefix] = $this->toArray();
                                $kq[] = $itemss;
                            }else{
                                $kq[] = $itemss;
                            }
                            $j++;
                        }
                        $outPut[$diretory[0]][$i][$diretory[1]] = $kq;
                        $i++;
                    }
                    return $outPut;
                }
                if(strlen($preFix) == 4){
                    $diretory = explode("|", $diretory);
                    $outPut = $arrTable;
                    $i=0;
                    foreach($arrTable[$diretory[0]] as $items){
                        $j=0;
                        foreach($items[$diretory[1]] as $itemss){
                            unset($kq);
                            $k=0;
                            foreach($itemss[$diretory[2]] as $itemsss){
                                unset($sql);
                                $sql = array();
                                if(!empty($where)){
                                    foreach($where as $k => $v){
                                        if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                                    }
                                }
                                switch($method){
                                    case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($itemsss[$keyParent]) : $itemsss[$keyParent]) . "'"; break;
                                    case 'in' : $sql[] = $keyChild . " in (" . $itemsss[$keyParent] . ")"; break;
                                    case 'not_in' : $sql[] = $keyChild . " not in (" . $itemsss[$keyParent] . ")"; break;
                                    case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                    case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                    case 'regexp' :
                                    $sql[] = $keyChild . " like binary '%^(" . $itemsss[$keyParent] . ")$%'";
                                    case 'not_in_regexplower' :
                                    $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($itemsss[$keyParent])))) . "')"; break;
                                    case 'regexplower' :
                                    $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($itemsss[$keyParent]) . ")$%'";
                                    //$sql[] = "'".$itemsss[$keyParent] . "' regexp " . $keyChild;
                                    break;
                                }
                                $sql=@implode(" and ",$sql);
                                $this->find($tableChild, $sql, $orderBy, $limit, $select);
                                if ($this->count() != 0) {
                                    $itemsss[$preFix . $namePrefix] = $this->toArray();
                                    $kq[] = $itemsss;
                                }else{
                                    $kq[] = $itemsss;
                                }
                                $k++;
                            }
                            $outPut[$diretory[0]][$i][$diretory[1]][$j][$diretory[2]] = $kq;
                            $j++;
                        }
                        $i++;
                    }
                    return $outPut;
                }
                if(strlen($preFix) == 5){
                    $diretory = explode("|", $diretory);
                    $outPut = $arrTable;
                    $i=0;
                    foreach($arrTable[$diretory[0]] as $items){
                        $j=0;
                        foreach($items[$diretory[1]] as $itemss){
                            $k=0;
                            foreach($itemss[$diretory[2]] as $itemsss){
                                unset($kq);
                                $l=0;
                                foreach($itemsss[$diretory[3]] as $itemssss){
                                    unset($sql);
                                    $sql = array();
                                    if(!empty($where)){
                                        foreach($where as $k => $v){
                                            if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                                        }
                                    }
                                    switch($method){
                                        case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($itemssss[$keyParent]) : $itemssss[$keyParent]) . "'"; break;
                                        case 'in' : $sql[] = $keyChild . " in (" . $itemssss[$keyParent] . ")"; break;
                                        case 'not_in' : $sql[] = $keyChild . " not in (" . $itemssss[$keyParent] . ")"; break;
                                        case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                        case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                        case 'regexp' :
                                        $sql[] = $keyChild . " like binary '%^(" . $itemssss[$keyParent] . ")$%'";
                                        case 'not_in_regexplower' :
                                        $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($itemssss[$keyParent])))) . "')"; break;
                                        case 'regexplower' :
                                        $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($itemssss[$keyParent]) . ")$%'";
                                        //$sql[] = "'".$itemssss[$keyParent] . "' regexp " . $keyChild;
                                        break;
                                    }
                                    $sql=@implode(" and ",$sql);
                                    $this->find($tableChild, $sql, $orderBy, $limit, $select);
                                    if ($this->count() != 0) {
                                        $itemssss[$preFix . $namePrefix] = $this->toArray();
                                        $kq[] = $itemssss;
                                    }else{
                                        $kq[] = $itemssss;
                                    }
                                    $l++;
                                }
                                $outPut[$diretory[0]][$i][$diretory[1]][$j][$diretory[2]][$k][$diretory[3]] = $kq;
                                $k++;
                            }
                            $j++;
                        }
                        $i++;
                    }
                    return $outPut;
                }
                if(strlen($preFix) == 6){
                    $diretory = explode("|", $diretory);
                    $outPut = $arrTable;
                    $i=0;
                    foreach($arrTable[$diretory[0]] as $items){
                        $j=0;
                        foreach($items[$diretory[1]] as $itemss){
                            $k=0;
                            foreach($itemss[$diretory[2]] as $itemsss){
                                $l=0;
                                foreach($itemsss[$diretory[3]] as $itemssss){
                                    unset($kq);
                                    $m=0;
                                    foreach($itemssss[$diretory[4]] as $itemsssss){
                                        unset($sql);
                                        $sql = array();
                                        if(!empty($where)){
                                            foreach($where as $k => $v){
                                                if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                                            }
                                        }
                                        switch($method){
                                            case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($itemsssss[$keyParent]) : $itemsssss[$keyParent]) . "'"; break;
                                            case 'in' : $sql[] = $keyChild . " in (" . $itemsssss[$keyParent] . ")"; break;
                                            case 'not_in' : $sql[] = $keyChild . " not in (" . $itemsssss[$keyParent] . ")"; break;
                                            case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                            case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                            case 'regexp' :
                                            $sql[] = $keyChild . " like binary '%^(" . $itemsssss[$keyParent] . ")$%'";
                                            case 'not_in_regexplower' :
                                            $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($itemsssss[$keyParent])))) . "')"; break;
                                            case 'regexplower' :
                                            $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($itemsssss[$keyParent]) . ")$%'";
                                            //$sql[] = "'".$itemsssss[$keyParent] . "' regexp " . $keyChild;
                                            break;
                                        }
                                        $sql=@implode(" and ",$sql);
                                        $this->find($tableChild, $sql, $orderBy, $limit, $select);
                                        if ($this->count() != 0) {
                                            $itemsssss[$preFix . $namePrefix] = $this->toArray();
                                            $kq[] = $itemsssss;
                                        }else{
                                            $kq[] = $itemsssss;
                                        }
                                        $m++;
                                    }
                                    $outPut[$diretory[0]][$i][$diretory[1]][$j][$diretory[2]][$k][$diretory[3]][$l][$diretory[4]] = $kq;
                                    $l++;
                                }
                                $k++;
                            }
                            $j++;
                        }
                        $i++;
                    }
                    return $outPut;
                }
                if(strlen($preFix) == 7){
                    $diretory = explode("|", $diretory);
                    $outPut = $arrTable;
                    $i=0;
                    foreach($arrTable[$diretory[0]] as $items){
                        $j=0;
                        foreach($items[$diretory[1]] as $itemss){
                            $k=0;
                            foreach($itemss[$diretory[2]] as $itemsss){
                                $l=0;
                                foreach($itemsss[$diretory[3]] as $itemssss){
                                    $m=0;
                                    foreach($itemssss[$diretory[4]] as $itemssss){
                                        unset($kq);
                                        $n=0;
                                        foreach($itemssss[$diretory[5]] as $itemssssss){
                                            unset($sql);
                                            $sql = array();
                                            if(!empty($where)){
                                                foreach($where as $k => $v){
                                                    if ($k) { $sql[] = $k . " = " . $v; } else { $sql[] = $v; }
                                                }
                                            }
                                            switch($method){
                                                case 'equal' : $sql[] = $keyChild . " = '" . (($isCompareLowerCase) ? strtolower_utf8($itemssssss[$keyParent]) : $itemssssss[$keyParent]) . "'"; break;
                                                case 'in' : $sql[] = $keyChild . " in (" . $itemssssss[$keyParent] . ")"; break;
                                                case 'not_in' : $sql[] = $keyChild . " not in (" . $itemssssss[$keyParent] . ")"; break;
                                                case 'in_regexp' : $sql[] = $keyChild . " in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                                case 'not_in_regexp' : $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex($arrTable[$keyParent]))) . "')"; break;
                                                case 'regexp' :
                                                $sql[] = $keyChild . " like binary '%^(" . $itemssssss[$keyParent] . ")$%'";
                                                case 'not_in_regexplower' :
                                                $sql[] = $keyChild . " not in ('" . implode("','",explode("|",clearregex(strtolower_utf8($itemssssss[$keyParent])))) . "')"; break;
                                                case 'regexplower' :
                                                $sql[] = $keyChild . " like binary '%^(" . strtolower_utf8($itemssssss[$keyParent]) . ")$%'";
                                                //$sql[] = "'".$itemssssss[$keyParent] . "' regexp " . $keyChild;
                                                break;
                                            }
                                            $sql=@implode(" and ",$sql);
                                            $this->find($tableChild, $sql, $orderBy, $limit, $select);
                                            if ($this->count() != 0) {
                                                $itemssssss[$preFix . $namePrefix] = $this->toArray();
                                                $kq[] = $itemssssss;
                                            }else{
                                                $kq[] = $itemssssss;
                                            }
                                            $n++;
                                        }
                                        $outPut[$diretory[0]][$i][$diretory[1]][$j][$diretory[2]][$k][$diretory[3]][$l][$diretory[4]][$m][$diretory[5]] = $kq;
                                        $m++;
                                    }
                                    $l++;
                                }
                                $k++;
                            }
                            $j++;
                        }
                        $i++;
                    }
                    return $outPut;
                }
            }
        }
    }
    function getColumnType ($tableName, $columnName) {
        $this->query("SHOW FIELDS FROM {$tableName}");
        $result = $this->toArray();
        if (empty($result) === FALSE) {
            $field = Inc_Utility::arraySearch($result, "Field == '{$columnName}'", FALSE);
            if (empty($field) === FALSE) {
                return $field['Type'];
            }
        }
        return FALSE;
    }
    function getSearch ($config) {
        $query = array();
        if (empty($config['search']) === FALSE) {
            foreach ($config['search'] as $k => $v) {
                if (empty($v) === FALSE) {
                    $type = $this->getColumnType($this->tableName, $k);
                    if ($type !== FALSE) {
                        if (preg_match('/^(text|varchar|char|tinytext|mediumtext|longtext)/', $type) === 1) {
                            $query[] = $this->tableName . ".{$k} LIKE '%{$v}%'";
                        } else {
                            $query[] = $this->tableName . ".{$k} = '{$v}'";
                        }
                    } else {
                        $this->getCustomSearch($query, $config); // can extend here?
                    }
                }
            }
        }
        return $query;
    }
    function getSort ($config) {
        $sort = array();
        if (empty($config['sort']) === FALSE) {
            foreach ($config['sort'] as $k => $v) {
                if ($v === 'asc' || $v === 'desc') {
                    if (strpos($k, '.') === FALSE) {
                        $sort[] = $this->tableName . ".{$k} {$v}";
                    } else {
                        $sort[] = "{$k} {$v}";
                    }
                }
            }
        } else {
            $sort[] = $this->tableName . "." . $this->dbDefaultOrder . " desc";
        }
        return $sort;
    }
    /**
     * $getAll = true => return count
     * $getAll = false => return items
     * $config: search || sort
     * $from && $select use when need join
     */
    function getList ($getAll = FALSE, $numStart = 0, $perPage = 0, $config = array(), $from = null, $select = '*', $groupBy = '', $having = '') {
        $query = $this->getSearch($config);
        $sort = $this->getSort($config);
        if (empty($from) === TRUE) {
            $from = $this->tableName;
        }
        $numStart = (empty($numStart) === FALSE) ? $numStart : 0;
        $perPage = (empty($perPage) === FALSE) ? $perPage : 0;
        if (empty($numStart) &&  empty($perPage)) {
            $limit = null;
        } else {
            $limit = $numStart . ',' . $perPage;
        }
        if ($getAll === TRUE) {
            if (empty($select) === FALSE && is_array($select)) {
                $select = implode(', ', $select);
            }
            /**
             * $select always string
             */
            if ($perPage > 0) {
                $select = array('SQL_CALC_FOUND_ROWS ' . $select);
            } else {
                $select = array($select);
            }
        }
        $this->find($from, implode(' and ', $query), implode(', ', $sort), $limit, $select, $groupBy, $having);
        return $this->toArray();
    }
    /**
     * $ids = array(1, 2, 3)
     */
    function deleteIds ($ids) {
        $this->delete($this->tableName, $this->_id . ' in (' . implode(',', $ids) . ')');
    }
    function getListCombobox ($combo = array('' => 'Choose value'), $groupStore = NULL) {
        if ($groupStore === NULL) {
            $this->find($this->tableName, NULL, $this->dbDefaultOrder . ' desc');
            $groupStore = $this->toArray();
        }
        if (empty($groupStore) === FALSE) {
            foreach ($groupStore as $item) {
                $firstValue = $endValue = '';
                foreach ($item as $k => $v) {
                    $array_first = array('_id');
                    $array_end   = array('name', 'title', 'username', 'fullname');
                    if (in_array($k, $array_first)) {
                        $firstValue = (string) $v;
                    } else if (in_array($k, $array_end)) {
                        $endValue = (string) $v;
                    }
                }
                $combo[$firstValue] = $endValue;
            }
        }
        return $combo;
    }
    function getListComboboxWhere ($combo = array('' => 'Choose value'), $groupStore = NULL, $lang = NULL, $where = NULL, $oderBy = NULL, $limit = NULL) {
            if ($groupStore === NULL) {
                if (empty($oderBy) === TRUE) {
                    $oderBy = $this->dbDefaultOrder . ' desc';
                }
                $this->find($this->tableName, $where, $oderBy, $limit);
                $groupStore = $this->toArray();
            }
            if (empty($groupStore) === FALSE) {
                foreach ($groupStore as $item) {
                    $firstValue = $endValue = '';
                    foreach ($item as $k => $v) {
                        $array_first = array('_id');
                        $array_end = array('name' . $lang, 'title' . $lang, 'username', 'fullname');
                        if (in_array($k, $array_first)) {
                            $firstValue = (string) $v;
                        } else if (in_array($k, $array_end)) {
                            $endValue = (string) $v;
                        }
                    }
                    $combo[$firstValue] = $endValue;
                }
            }
            return $combo;
        }
    public function findTop ($field, $isReturnNumber = TRUE, $isTop = TRUE, $isNextTop = FALSE) {
        $limit = 1;
        if ($isNextTop === TRUE) {
            $limit = 2;
        }
        $this->find(
            $this->tableName, // from
            NULL, // where
            $field . ' ' . (($isTop) ? 'desc' : 'asc'),  // order by
            $limit, // limit
            array(($isReturnNumber) ? ($field) : '*') // select
        );
        if ($isNextTop === TRUE) {
            $result = $this->toArray();
            if (empty($result) === FALSE) {
                $result = $result[1];
            }
        } else {
            $result = $this->toRow();
        }
        if (empty($result) === FALSE) {
            return ($isReturnNumber) ? intval($result[$field]) : $result; // array of ForumUser objects
        }
        return ($isReturnNumber) ? 0 : array(); // array of ForumUser objects
    }
    public function findNextTop ($field, $isReturnNumber = TRUE, $isTop = TRUE) {
        return $this->findTop($field, $isReturnNumber, $isTop, TRUE);
    }
    public function findBot ($field, $isReturnNumber = TRUE) {
        return $this->findTop($field, $isReturnNumber, FALSE);
    }
    public function findNextBot ($field, $isReturnNumber = TRUE) {
        return $this->findTop($field, $isReturnNumber, FALSE, TRUE);
    }
    public function generateStrLang () {
        $lang = $GLOBALS['lang'];
        return ($lang == 'vn') ? "_{$lang}" : NULL;
    }
}
