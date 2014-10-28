<?php
class Models_Mongo extends ConnectDb_Mongo {
    /**
     * $args[0]: $id
     */
    function __construct () {
        $this->connect();
        $this->dbDefaultOrder = '_id';
        $this->field = array();
        $this->loadCollection();
    }
    function loadCollection ($collectionName = '') {
        $field = array();
        if (empty($collectionName) === TRUE) {
            if (isset($this->collectionName) === TRUE) {
                require BASE_PATH . '/Collections/' . $this->collectionName . '.php';
            }
        } else {
            require BASE_PATH . '/Collections/' . $collectionName . '.php';
        }
        $this->field = array_merge($this->field, $field);
    }
    /**
     * $getAll = true => return count
     * $getAll = false => return items
     * $config: search || sort
     */
    function getList ($getAll = FALSE, $numStart = null, $perPage = null, $config = array()) {
        $query = array();
        $sort = array();
        if (empty($config['search']) === FALSE) {
            foreach ($config['search'] as $k => $v) {
                if (empty($v) === FALSE) {
                    $query[$k] = $v;
                }
            }
        }
        if (empty($config['sort']) === FALSE) {
            foreach ($config['sort'] as $k => $v) {
                if ($v === 'asc' || $v === 'desc') {
                    if ($v === 'asc') {
                        $v = 1;
                    } else {
                        $v = -1;
                    }
                    $sort[$k] = $v;
                }
            }
        } else {
            $sort[$this->dbDefaultOrder] = -1;
        }
        $cursor = $this->getCollection()->find($query);
        if ($getAll === TRUE) {
            return $cursor->count();
        } else {
            return $cursor->skip($numStart)->limit($perPage)->sort($sort);
        }
    }
    function findTop ($field, $isReturnNumber = TRUE, $isTop = TRUE) {
        $sort = array($field => ($isTop === TRUE) ? -1 : 1);
        $cursor = $this->getCollection()->find(array(), array('_id' => 0, $field => 1))->sort($sort)->limit(1);
        $record = iterator_to_array($cursor, FALSE); // $iterator, $use_keys
        return (empty($record) === FALSE) ? $record[0][$field] : 0;
    }
    public function findBot ($field, $isReturnNumber = TRUE) {
        return $this->findTop($field, $isReturnNumber, FALSE);
    }
    function retriveRef (&$items, $fields = array()) {
        if (empty($items) === FALSE) {
            foreach ($items as &$item) {
                foreach ($fields as $v) {
                    if (empty($item[$v]) === FALSE) {
                        $item[$v] = $this->getDb()->getDBRef($item[$v]);
                    }
                }
            }
            unset($item);
        }
    }
    function retriveIds ($items, $fields) {
        $ids = array();
        if (empty($items) === FALSE) {
            foreach ($items as $item) {
                if (empty($item[$fields]) === FALSE) {
                    $ids[] = $item[$fields]['_id'];
                }
            }
        }
        return $ids;
    }
    /**
     * $ids is array of ids
     * $key is '_id' | 'cate._id' | 'type._id' ...
     */
    function deleteIds ($ids, $key = '_id') {
        if (empty($ids) === FALSE) {
            $this->getCollection()->remove(array(
                $key => array(
                    '$in' => $ids
                )
            ));
        }
    }
    function getListCombobox ($combo = array('' => 'Choose value'), $groupStore = NULL) {
        if ($groupStore === NULL) {
            $sort = array($this->dbDefaultOrder => -1);
            $groupStore = $this->getCollection()->find()->sort($sort);
        }
        if (empty($groupStore) === FALSE) {
            foreach ($groupStore as $item) {
                $firstValue = $endValue = '';
                foreach ($item as $k => $v) {
                    $array_first = array('_id');
                    $array_end   = array('name', 'title', 'username');
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
}
