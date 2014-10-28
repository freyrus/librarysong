<?php
class BackendParentController extends Controller {
    function __construct () {
        parent::__construct();
        $GLOBALS['PERPAGE']            = (empty($GLOBALS['FILE_XML_CONFIG']->perpage) === FALSE) ? intval($GLOBALS['FILE_XML_CONFIG']->perpage) : 50;
        $GLOBALS['SCROLLPAGE']         = 5;
        $GLOBALS['INACTIVE_PAGE_TEXT'] = 'id="current_page"';
        $GLOBALS['PREVIOUS_PAGE_TEXT'] = '&lt; ';
        $GLOBALS['NEXT_PAGE_TEXT']     = ' &gt; ';
        $GLOBALS['FIRST_PAGE_TEXT']    = ' &lt;&lt; ';
        $GLOBALS['LAST_PAGE_TEXT']     = ' &gt;&gt; ';
        $GLOBALS['PAGER_URL_LAST']     = '';
        $this->view->canMoving         = TRUE;
        $this->view->field             = array();
        $this->view->pageTitle         = NULL;
        if (isset($this->mainModel) === FALSE) {
            $this->mainModel = 'model';
        }
        $adminModel = new Model_Admin();
        if (empty($_SESSION['logged']) === TRUE || empty($_SESSION['logged'][$adminModel->role]) === TRUE) {
            Inc_Utility::redirect(BACKEND_URL . '/login/index/redirectUrl/' . Inc_Utility::getRedirectUrl());
        }
        $this->checkAllowed();
    }
    /**
     * check access control: each group has own controller and action, if they don't have role appropriate
     * => redirect them go to login page
     */
    function checkAllowed () {
        $validRole = Inc_Utility::isHasValidRole(@Inc_String::trimQueryString($_SERVER['REDIRECT_URL']));
        /**
         * if user not have permission => redirect user to login page
         */
        if ($validRole['isValid'] === FALSE) { ?>
            <script type="text/javascript">
            alert('You don\'t have permission to access this action!');
            window.location.href = "<?php echo BACKEND_URL ?>/system/logout";
            </script><?php
            exit();
        }
    }
    function processConfigSearch (&$result, $fieldReturn, $field, $type = 'string') {
        if (empty($_GET['search-' . $field]) === FALSE) {
            switch ($type) {
                case 'mongoId':
                    $result[$fieldReturn] = new MongoId($_GET['search-' . $field]); break;
                case 'mongoString':
                    $result[$fieldReturn] = new MongoRegex("/^" . $_GET['search-' . $field] . "/i"); break;
                case 'int':
                    $result[$fieldReturn] = intval($_GET['search-' . $field]); break;
                default: // string
                    $result[$fieldReturn] = $_GET['search-' . $field]; break;
            }
        }
    }
    function processConfigSort (&$result, $nameValid = array()) {
        $p = $_GET;
        if (empty($p) === FALSE) {
            foreach ($p as $k => $v) { // foreach because we want order
                if (strpos($k, 'sort-') !== false) {
                    $this->view->canMoving = FALSE;
                    $name = substr($k, 5); // id or name
                    if (in_array($name, $nameValid) === TRUE) {
                        if ($name === 'id') $name = '_' . $name;
                        $result[$name] = $v;
                    }
                }
            }
        }
    }
    function getIdDelete () {
        if (empty($_POST['selectAction']) === FALSE) {
            if (empty($_POST['option']) === FALSE) {
                $ids = array();
                foreach ($_POST['option'] as $item) {
                    if ($this->mainModel === 'mongo') {
                        $ids[] = new MongoId($item);
                    } else {
                        $ids[] = $item;
                    }
                }
                return $ids;
            }
        }
        return array();
    }
    function processDelete ($ids) {
        $isHasError = FALSE;
        if (empty($ids) === FALSE) {
            if ($this->mainModel === 'mongo') {
                try {
                    $this->mongo->deleteIds($ids);
                } catch (MongoCursorException $e) {
                    $isHasError = TRUE;
                    $this->view->_errors[] = $e->getMessage();
                }
            } else {
                try {
                    $this->model->deleteIds($ids);
                } catch (PDOException $e) {
                    $isHasError = TRUE;
                    $this->view->_errors[] = $e->getMessage();
                }
            }
        }
        if ($isHasError === TRUE) {
            $this->view->loadView('Views/common/_error');
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']); // back
        }
    }
    function insertFile ($file) {
        $fileName = Inc_String::removeAccents($file['name']);
        $result = NULL;
        if (empty($fileName) === FALSE) {
            $result = $this->folderFiles . '/' . FOLDER . '/';
            $path = BASE_FRONT_PATH . '/' . $result;
            if (is_dir($path) === FALSE) {
                mkdir($path, 0777, TRUE); // create recursive folder
            }
            $fileName = Inc_Utility::getNewFileName($path, $fileName);
            $fullPath = $path . $fileName;
            Inc_Utility::uploadImage($file["tmp_name"] , $fullPath); // fullpath
            $result .= $fileName;
        }
        return $result;
    }
    function insertFtpFile ($file) {
        $fileName = Inc_String::removeAccents($file['name']);
        $result = NULL;
        if (empty($fileName) === FALSE) {
            $result = $this->folderFiles;
            $fileName = Inc_Utility::getNewFileName(NULL, $fileName);
            $result .= $fileName;
            $ftp = new ConnectFtp();
            $ftp->move($file["tmp_name"], $result);
        }
        return $result;
    }
    function updateFile ($oldFile, $newFile) {
        $result = $oldFile;
        if (empty($newFile['name']) === FALSE) {
            // delete old file
            $this->deleteOldFile($oldFile);
            // insert new File
            $result = $this->insertFile($newFile);
        }
        return $result;
    }
    function updateFtpFile ($oldFile, $newFile) {
        $result = $oldFile;
        if (empty($newFile['name']) === FALSE) {
            // delete old file
            $this->deleteOldFtpFile($this->folderFiles . $oldFile);
            // insert new File
            $result = $this->insertFtpFile($newFile);
        }
        return $result;
    }
    function insertImage ($file) {
        require_once BASE_FRONT_PATH . '/cropimage/function.php';
        require_once BASE_FRONT_PATH . '/cropimage/class.resizeimage.php';
        require_once BASE_FRONT_PATH . '/cropimage/class.cropcanvas.php';
        $fileName = Inc_String::removeAccents($file['name']);
        $result = NULL;
        if (empty($fileName) === FALSE) {
            $result = $this->folderImages . '/' . FOLDER . '/';
            $path = BASE_FRONT_PATH . '/' . $result;
            if (is_dir($path) === FALSE) {
                mkdir($path, 0777, TRUE); // create recursive folder
            }
            $fileName = Inc_Utility::getNewFileName($path, $fileName);
            $fullPath = $path . $fileName;
            Inc_Utility::uploadImage($file["tmp_name"] , $fullPath); // fullpath
            $result .= $fileName;
            // save to thumbnail
            $resize_new_file   = $this->getImageFolderPath($fileName, TRUE);
            $resize_image_type = get_image_type($fullPath);
            $resize_image      = new ResizeImage();
            $resize_image->load($fullPath);
            $resize_image->resizeToWidth(THUMB_WIDTH);
            $resize_image->save($resize_new_file, $resize_image_type, THUMB_COMPRESS); // filename, extension, compression
        }
        return $result;
    }
    function updateImage ($oldFile, $newFile) {
        $result = $oldFile;
        if (empty($newFile['name']) === FALSE) {
            // delete old file
            $this->deleteOldImage($oldFile);
            // insert new File
            $result = $this->insertImage($newFile);
        }
        return $result;
    }
    function getImageFolderPath ($fileName, $isResized = FALSE, $isRelative = TRUE) {
        if ($isRelative === TRUE) {
            $baseFront = BASE_FRONT_PATH;
        } else {
            $baseFront = BASE_FRONT;
        }
        if ($isResized === FALSE) {
            $path = $baseFront . '/' . $this->folderImages . '/' . FOLDER . '/';
        } else {
            $path = $baseFront . '/' . RESIZE_FOLDER . '-' . $this->folderImages . '/' . FOLDER . '/';
        }
        if (is_dir($path) === FALSE) {
            mkdir($path, 0777, TRUE); // create recursive folder
        }
        return $path . basename($fileName);
    }
    function deleteOldImage ($fileName) {
        if (empty($fileName) === FALSE) {
            unlink($this->getImageFolderPath($fileName)); // $file, $resized, $relative
            unlink($this->getImageFolderPath($fileName, TRUE)); // $file, $resized, $relative
        }
    }
    function deleteOldFile ($fileName) {
        if (empty($fileName) === FALSE) {
            unlink(BASE_FRONT_PATH . '/' . $fileName);
        }
    }
    function deleteOldFtpFile ($fileName) {
        if (empty($fileName) === FALSE) {
            $ftp = new ConnectFtp();
            $ftp->delete($fileName);
        }
    }
    public function moveTopAction () {
        $this->moveTopBottom(">", "<", "asc", "desc");
        $this->indexAction(); // show bug if has errors
    }
    public function moveBottomAction () {
        $this->moveTopBottom("<", ">", "desc", "asc");
        $this->indexAction(); // show bug if has errors
    }
    private function moveTopBottom ($next, $previous, $nextOrderBy, $previousOrderBy) {
        $nextItem = NULL;
        $id = $GLOBALS['PARAMS']['id'];
        $sequenceCurr = $GLOBALS['PARAMS']['sequence'];
        $config['search'] = $this->getConfigSearch();
        $config['sort'] = $this->getConfigSort();
        if (empty($config['search']) === FALSE || empty($config['sort']) === FALSE) {
            $this->getList($config, 0);
            $items = $this->view->items;
            if (empty($items) === FALSE) {
                if ($next == '>') { /* up */
                    $items = array_reverse($items);
                }
                $isNext = FALSE;
                foreach ($items as $item) {
                    if ($isNext === TRUE) {
                        $nextItem = $item;
                        break;
                    }
                    if ($item['sequence'] == $sequenceCurr) {
                        $isNext = TRUE;
                    }
                }
            }
        } else {
            $this->model->find(
                $this->model->tableName, // from
                'sequence ' .$next . ' ' . $sequenceCurr,  // where
                'sequence ' . $nextOrderBy, // order by
                '1', // limit
                array($this->model->_id, $this->model->sequence) // select
            );
            $nextItem = $this->model->toRow();
        }
        if ($nextItem) {
            if ($nextItem['sequence'] != $sequenceCurr) {
                try {
                    // update current _id
                    $this->model->update($this->model->tableName, array($this->model->sequence => $nextItem[$this->model->sequence]), $this->model->_id . ' = ' . $id);
                    // update next _id
                    $this->model->update($this->model->tableName, array($this->model->sequence => $sequenceCurr), $this->model->_id . ' = ' . $nextItem[$this->model->_id]);
                    header('Location: ' . $_SERVER['HTTP_REFERER']); // back
                } catch (PDOException $e) {
                    ?>
                    <script type="text/javascript">
                    alert('<?php echo $e->getMessage() ?>');
                    window.history.back();
                    </script>
                    <?php
                    exit();
                }
            } else { ?>
                <script type="text/javascript">
                alert('Two items have same sequence, please contact developer to fix the problem!');
                window.history.back();
                </script>
                <?php
                exit();
            }
        }
    }
    private function mongoMoveTopBottom ($next, $previous, $nextOrderBy, $previousOrderBy) {
        $id = $GLOBALS['PARAMS']['id'];
        $sequenceCurr = intval($GLOBALS['PARAMS']['sequence']);
        $cursor = $this->mongo->getCollection()->find(array(
            'sequence' => array(
                $next => $sequenceCurr
            )
        ), array(
            'sequence' => 1
        ))->sort(array('sequence' => $nextOrderBy))->limit(1);
        $next = iterator_to_array($cursor, FALSE); // $iterator, $use_keys
        if (empty($next) === FALSE) {
            $next = $next[0];
            try {
                // update current _id
                $this->mongo->getCollection()->update(
                    array('_id' => new MongoId($id)),
                    array('$set' => array(
                        'sequence' => intval($next['sequence'])
                    ))
                );
                // update next _id
                $this->mongo->getCollection()->update(
                    array('_id' => $next['_id']),
                    array('$set' => array(
                        'sequence' => $sequenceCurr
                    ))
                );
                header('Location: ' . $_SERVER['HTTP_REFERER']); // back
            } catch (PDOException $e) {
                ?>
                <script type="text/javascript">
                alert('<?php echo $e->getMessage() ?>');
                window.history.back();
                </script>
                <?php
                exit();
            }
        }
    }
    public function mongoMoveTopAction () {
        $this->mongoMoveTopBottom('$gt', '$lt', 1, -1);
        $this->indexAction(); // show bug if has errors
    }
    public function mongoMoveBottomAction () {
        $this->mongoMoveTopBottom('$lt', '$gt', -1, 1);
        $this->indexAction(); // show bug if has errors
    }
    function getList ($config = array(), $perPage = NULL) {
        $this->view->currentPage = $currentPage = Inc_Utility::getPage();
        $this->view->perPage = $perPage = (isset($perPage) === TRUE ? $perPage : $GLOBALS['PERPAGE']);
        if ($this->mainModel === 'mongo') {
            $this->view->items = $this->mongo->getList(FALSE, ($currentPage - 1) * $perPage, $perPage, $config);
            $totalRecord = $this->mongo->getList(TRUE, null, null, $config);
        } else {
            $this->view->items = $this->model->getList(TRUE, ($currentPage - 1) * $perPage, $perPage, $config);
            $totalRecord = $this->model->foundRow();
        }
        if ($perPage > 0) {
            // create pagination
            $kgPagerOBJ = new Inc_KgPager;
            $kgPagerOBJ->pager_set($totalRecord, $perPage, $currentPage);
            $this->view->kgPagerOBJ = $kgPagerOBJ;
        }
    }
    function populateFields () {
        if ($this->mainModel === 'mongo') {
            $m = $this->mongo;
        } else {
            $m = $this->model;
        }
        $args = func_get_args();
        if (empty($m->field) === FALSE && empty($args) === FALSE) {
            foreach ($m->field as $item) {
                $this->view->field[$item] = NULL;
                foreach ($args as $arr) {
                    if (is_array($arr) === TRUE && array_key_exists($item, $arr) === TRUE) {
                        $this->view->field[$item] = $arr[$item];
                    }
                }
            }
        }
        /**
         * add redirectUrl field
         */
        $this->view->field['redirectUrl'] = NULL;
        if (empty($GLOBALS['PARAMS']['redirectUrl']) === FALSE) {
            $this->view->field['redirectUrl'] =$GLOBALS['PARAMS']['redirectUrl'];
        }
    }
    function finishEditProcess () {
        if (empty($_POST['redirectUrl']) === FALSE) {
            Inc_Utility::redirect(Inc_Utility::returnRedirectUrl($_POST['redirectUrl']));
        } else {
            ?>
            <script language="javascript">
            if (window.opener) {
                window.opener.location.href = window.opener.location.href; // empty cache
                window.close();
            } else {
                window.location.href = window.location.href; // empty cache
            }
            </script>
            <?php
            exit();
        }
    }
    function finishAddProcess () {
        if (empty($_POST['redirectUrl']) === FALSE) {
            Inc_Utility::redirect();
        } else {
            ?>
            <script language="javascript">
            if (window.opener) { window.opener.location.href = window.opener.location.href; } // empty cache
            window.location.href = window.location.href; // empty cache
            </script>
            <?php
            exit();
        }
    }
    function processCheckModuleBackend () {
        $this->view->action[] = 'Start...';
        $listModule = array();
        $listController = array();
        $path = BASE_PATH . '/Modules';
        if ($handle = opendir($path)) {
            while (false !== ($moduleName = readdir($handle))) {
                if (Inc_Helper::validateAlphanumericUnderscore($moduleName)) {
                    $listModule[] = $moduleName;
                    if ($handle1 = opendir($path . '/' . $moduleName . '/Controllers')) {
                        $controllerName = array();
                        while (false !== ($entry1 = readdir($handle1))) {
                            $l = strrpos($entry1, 'Controller.php');
                            if ($l !== FALSE) {
                                // get controller name
                                $controllerName[] = substr($entry1, 0, $l);
                            }
                        }
                        $listController[$moduleName] = $controllerName;
                        closedir($handle1);
                    }
                }
            }
            closedir($handle);
        }
        $router = array();
        if (defined('BASE_CONFIG') === TRUE) {
            require_once BASE_CONFIG . '/router.php';
        } else {
            require_once BASE_PATH . '/router.php' ;
        }
        if (empty($listRouter) === FALSE) {
            foreach ($listRouter as $k => $v) {
                if (strpos($k, '/') !== 0) { // string
                    $uri = explode('/', $k);
                    $c = Inc_Utility::processModuleName($uri[0]);
                    if (in_array($uri[0], $listModule)) {
                        $this->view->action[] = 'Module name conflict: ' . $uri[0];
                    } else if (in_array($c, $listController['default'])) {
                        $this->view->action[] = 'Controller name conflict: ' . $c . 'Controller.php - in module default';
                    }
                } else {
                    $s = rtrim(ltrim($k, '/'), '/i');
                    $a = explode('/', $s);
                    foreach ($listModule as $item) {
                        if (strpos($a[0], $item) !== FALSE) {
                            $this->view->action[] = 'Module name conflict: ' . $item;
                        }
                    }
                    foreach ($listController['default'] as $item) {
                        $c = Inc_Utility::processModuleName(rtrim(rtrim(ltrim(ltrim($a[0], '^'), '('), '\\'), ')'));
                        if (strpos($c, $item) !== FALSE) {
                            $this->view->action[] = 'Controller name conflict: ' . $c . 'Controller.php - in module default';
                        }
                    }
                }
            }
        }
        $this->view->action[] = '...End';
    }
    function processResourceBackend ($path) {
        $this->view->action = array('Start...');
        $ignoreResource = array('Login', 'Backend', 'Role', 'Admin', 'System');
        $ignoreAction = array('logout', 'moveTop', 'moveBottom', 'mongoMoveTop', 'mongoMoveBottom');
        $moduleName = ucwords($GLOBALS['MODULE_NAME']);
        if ($handle = opendir($path)) {
            $controllerName = array();
            $actionName = array();
            $i = 0;
            while (false !== ($entry = readdir($handle))) {
                $l = strrpos($entry, 'Controller.php');
                if ($l !== FALSE) {
                    // get controller name
                    $controllerName[$i] = substr($entry, 0, $l);
                    if ($controllerName[$i] === $moduleName) { continue; }
                    // include controller
                    require_once $path . '/' . $entry;
                    // get class name
                    $className = $moduleName . '_' . $controllerName[$i] . 'Controller';
                    // call this class
                    $class =  new $className();
                    // get list function from this class
                    $function = get_class_methods($className);
                    if (empty($function) === FALSE) {
                        foreach ($function as $f) {
                            $p = strrpos($f, 'Action');
                            if ($p !== FALSE) {
                                // get action name
                                $actionName[$i][] = substr($f, 0, $p);
                            }
                        }
                    }
                    $i++;
                }
            }
            closedir($handle);
        }
        /**
         * remove resources in database if they are not exist
         */
        $resourceModel = new Model_Role_Resource;
        $allCurrController = $resourceModel->getByIdController(NULL);
        $controllerRemove = array();
        foreach ($allCurrController as $item) {
            if (in_array($item['name'], $controllerName) === FALSE) {
                $controllerRemove[] = $item['_id'];
                $this->view->action[] = '- Remove resource: ' . $item['name'];
            }
        }
        if (empty($controllerRemove) === FALSE) {
            $resourceModel->deleteIds($controllerRemove);
        }
        /**
         * insert resources
         */
        $cols = array('id_controller', 'name');
        foreach ($actionName as $k => $v) {
            if (in_array($controllerName[$k], $ignoreResource) === TRUE) {
                continue;
            }
            $currController = $resourceModel->getOneByName_And_IdController(array($controllerName[$k], NULL));
            if (empty($currController) === TRUE) {
                $item = array();
                $item['id_controller'] = NULL;
                $item['name'] = $controllerName[$k];
                $this->view->action[] = '+ Add resource: ' . $controllerName[$k];
                $idController = $resourceModel->insert($resourceModel->tableName, $item);
            } else {
                $idController = $currController['_id'];
                /**
                 * remove action in database if they are not exist
                 */
                $allCurrAction = $resourceModel->getByIdController($idController);
                $actionRemove = array();
                foreach ($allCurrAction as $item) {
                    if (in_array($item['name'], $actionName[$k]) === FALSE) {
                        $actionRemove[] = $item['_id'];
                        $this->view->action[] = '--- Remove action: ' . $item['name'] . ' in resource ' . $controllerName[$k];
                    }
                }
                if (empty($actionRemove) === FALSE) {
                    $resourceModel->deleteIds($actionRemove);
                }
            }
            if (empty($idController) === FALSE) {
                foreach ($v as $a) {
                    if (in_array($a, $ignoreAction) === TRUE) {
                        continue;
                    }
                    /**
                     * insert action
                     */
                    $item = array();
                    $item['id_controller'] = $idController;
                    $item['name'] = $a;
                    try {
                        $idAction = $resourceModel->insert($resourceModel->tableName, $item);
                        $this->view->action[] = '+++ Add action: ' . $a . ' in resource ' . $controllerName[$k];
                    } catch (Exception $e) {

                    }
                }
            }
        }
        $this->view->action[] = '...End';
    }
    function processCollectionBackend () {
        $mongo = new Models_Mongo;
        $list = $mongo->getDb()->getCollectionNames();
        try {
            foreach ($list as $collectionName) {
                $record = $mongo->getCollection($collectionName)->findOne();
                if (empty($record) === FALSE) {
                    $data = array('<?php');
                    foreach ($record as $field => $value) {
                        $data[] = '$field[\'' . $field . '\'] = \'' . $field . '\';';
                    }
                    $filePath = BASE_PATH . "/Collections/{$collectionName}.php";
                    if (is_file($filePath) === FALSE) {
                        file_put_contents($filePath, NULL);
                    }
                    file_put_contents($filePath, implode("\r\n", $data));
                }
            }
        } catch (Exception $e) {
            debug($e);
        }
    }
    function processModelBackend () {
        $model = new Models;
        $model->query('SHOW TABLES FROM ' . DBNAME);
        $tables = $model->toArray();
        try {
            foreach ($tables as $table) {
                $tableName = $table['Tables_in_' . DBNAME];
                $model->query("SHOW COLUMNS FROM {$tableName}");
                $columns = $model->toArray();
                $data = array('<?php');
                foreach ($columns as $col) {
                    $data[] = '$field[\'' . $col['Field'] . '\'] = \'' . $col['Field'] . '\';';
                }
                $filePath = BASE_PATH . "/Tables/{$tableName}.php";
                if (is_file($filePath) === FALSE) {
                    file_put_contents($filePath, NULL);
                }
                file_put_contents($filePath, implode("\r\n", $data));
            }
        } catch (Exception $e) {
            debug($e);
        }
    }
    function processAddRoleBackend () {
        $this->view->frmName = 'frmAdd';
        if (empty($_POST['frmName']) === FALSE && $_POST['frmName'] === $this->view->frmName && empty($_POST['name']) === FALSE) {
            if ($_POST['name'] === 'admin') {
                $this->view->_errors[] = 'Please type another name!';
            }
            if (empty($_POST['roles']) === TRUE) {
                $this->view->_errors[] = 'You must select at least one role';
            }
            if (empty($this->view->_errors) === TRUE) {
                try {
                    $item = array();
                    $item['name'] = Inc_Utility::clearFormat($_POST['name']);
                    $item['roles'] = serialize($_POST['roles']);
                    $this->model->insert($this->model->tableName, $item);
                    $this->finishAddProcess();
                } catch (PDOException $e) {
                    $this->view->_errors[] = $e->getMessage();
                }
            }
        }
        $this->populateFields($_POST);
        // get resources
        $this->resourceModel->find($this->resourceModel->tableName, NULL, "_id asc");
        $resources = $this->resourceModel->toArray();
        $this->view->resources = array();
        foreach ($resources as $item) {
            if (empty($item['id_controller']) === FALSE) {
                $this->view->resources[$item['id_controller']]['value'][$item['_id']] = $item['name'];
            } else {
                $this->view->resources[$item['_id']]['name'] = $item['name'];
            }
        }
    }
    function processEditRoleBackend () {
        $this->view->frmName = 'frmEdit';
        if (empty($_POST['frmName']) === FALSE && $_POST['frmName'] === $this->view->frmName && empty($_POST['name']) === FALSE) {
            if ($_POST['id'] === '1') {
                $this->view->_errors[] = 'Can\'t edit admin';
            }
            if (empty($_POST['roles']) === TRUE) {
                $this->view->_errors[] = 'You must select at least one role';
            }
            if (empty($this->view->_errors) === TRUE) {
                try {
                    $record = array();
                    $record['name'] = Inc_Utility::clearFormat($_POST['name']);
                    $record['roles'] = serialize($_POST['roles']);
                    $this->model->update($this->model->tableName, $record, '_id = ' . Inc_Utility::clearFormat($_POST['id']));
                    $this->finishEditProcess();
                } catch (PDOException $e) {
                    $this->view->_errors[] = $e->getMessage();
                }
            }
        }
        // get one field
        $this->model->find($this->model->tableName, '_id = ' . $GLOBALS['PARAMS']['id']);
        $this->view->field = $this->model->toRow();
        $this->view->field['roles'] = unserialize($this->view->field['roles']);
        $this->populateFields($this->view->field, $_POST);
        // get resources
        $this->resourceModel->find($this->resourceModel->tableName, NULL, "id_controller asc, {$this->resourceModel->dbDefaultOrder} asc");
        $resources = $this->resourceModel->toArray();
        $this->view->resources = array();
        foreach ($resources as $item) {
            if (empty($item['id_controller']) === FALSE) {
                $this->view->resources[$item['id_controller']]['value'][$item['_id']] = $item['name'];
            } else {
                $this->view->resources[$item['_id']]['name'] = $item['name'];
            }
        }
    }
}
