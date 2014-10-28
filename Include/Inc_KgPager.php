<?php
class Inc_KgPager {
    var $total_records = NULL;
    var $start = NULL;
    var $scroll_page = NULL;
    var $per_page = NULL;
    var $total_pages = NULL;
    var $current_page = NULL;
    var $page_links = NULL;
    var $lang = 'vn';
    var $functionHandleUrl = 'url';

    // preprocess url when pagination
    function url ($page) {
        $result = array_merge(array(
            'module' => $GLOBALS['MODULE_NAME'],
            'controller' => $GLOBALS['CONTROLLER_NAME'],
            'action' => $GLOBALS['ACTION_NAME'],
        ), $GLOBALS['PARAMS'], array('page' => $page));
        return url($result, 'default');
    }

    function urlBuildLink ($page) {
        $suffix = Inc_Utility::getSuffix('lang=' . $this->lang);
        return BASE_URL . '/'.$GLOBALS['URLNAME'] . '-' . Inc_Utility::buildLink(array('p' => $page)) . $suffix;
    }

    // total pages and essential variables
    function total_pages ($total_records, $scroll_page, $per_page, $current_page) {
        $this -> total_records = $total_records;
        $this -> scroll_page = $scroll_page;
        $this -> per_page = $per_page;
        if (!is_numeric($current_page)) {
            $this -> current_page = 1;
        } else {
            $this -> current_page = $current_page;
        }
        if ($this -> current_page == 1) $this -> start = 0; else $this -> start = ($this -> current_page - 1) * $this -> per_page;
        $this -> total_pages = ceil($this -> total_records / $this -> per_page);
    }

    // page links
    function page_links ($inactive_page_tag, $pager_url_last, $style) {
        if ($style === 1) {
            $this -> page_links = '<li><a>' . $this -> current_page." of ".$this -> total_pages . '</a></li>';
        } else if ($style === 2) {
            if ($this -> total_pages <= $this -> scroll_page) {
                if ($this -> total_records <= $this -> per_page) {
                    $loop_start = 1;
                    $loop_finish = $this -> total_pages;
                } else {
                    $loop_start = 1;
                    $loop_finish = $this -> total_pages;
                }
            } else {
                if ($this -> current_page < intval($this -> scroll_page / 2) + 1) {
                    $loop_start = 1;
                    $loop_finish = $this -> scroll_page;
                } else {
                    $loop_start = $this -> current_page - intval($this -> scroll_page / 2);
                    $loop_finish = $this -> current_page + intval($this -> scroll_page / 2);
                    if ($loop_finish > $this -> total_pages) $loop_finish = $this -> total_pages;
                }
            }
            for ($i = $loop_start; $i <= $loop_finish; $i++) {
                if ($i == $this -> current_page) {
                    $this -> page_links .= '<li '.$inactive_page_tag.'> '.$i.' </li>';
                } else {
                    $this -> page_links .= '<li><a href="' . call_user_func_array(array($this, $this->functionHandleUrl), array($i)) . '""> ' . $i . ' </a></li>';
                }
            }
        }
    }

    // previous page
    function previous_page ($previous_page_text, $pager_url_last) {
        if ($this -> current_page > 1) {
            $this -> previous_page = '<li><a href="' . call_user_func_array(array($this, $this->functionHandleUrl), array(Inc_Utility::getPage() - 1)) . '">'.$previous_page_text.'</a></li>';
        }
    }

    // next page
    function next_page ($next_page_text, $pager_url_last) {
        if ($this -> current_page < $this -> total_pages) {
            $page = Inc_Utility::getPage();
            if (!$page) {
                $page = 1;
            }
            $this -> next_page = '<li><a href="' . call_user_func_array(array($this, $this->functionHandleUrl), array($page + 1)) . '">'.$next_page_text.'</a></li>';
        }
    }

    // first page
    function first_page ($first_page_text, $pager_url_last) {
        if ($this -> current_page > 1) {
            $this -> first_page = '<li><a href="' . call_user_func_array(array($this, $this->functionHandleUrl), array(1)) . '">'.$first_page_text.'</a></li>'; // :)
        }
    }

    // last page
    function last_page ($last_page_text, $pager_url_last) {
        if ($this -> current_page < $this -> total_pages) {
            $this -> last_page = '<li><a href="' . call_user_func_array(array($this, $this->functionHandleUrl), array($this -> total_pages)) . '">'.$last_page_text.'</a></li>';
        }
    }

    /* pages functions set, how to use:
    $kgPagerOBJ = new Inc_KgPager;
    $kgPagerOBJ->lang = $this->lang;
    $kgPagerOBJ->functionHandleUrl = 'urlBuildLink';
    $kgPagerOBJ->pager_set($list['total'], $GLOBALS['PERPAGE'], $currentPage, $style);
     */
    function pager_set ($total_records, $per_page, $current_page, $style = 1) {
        $this -> total_pages($total_records, $GLOBALS['SCROLLPAGE'], $per_page, $current_page);
        $this -> page_links($GLOBALS['INACTIVE_PAGE_TEXT'], $GLOBALS['PAGER_URL_LAST'], $style);
        $this -> previous_page($GLOBALS['PREVIOUS_PAGE_TEXT'], $GLOBALS['PAGER_URL_LAST']);
        $this -> next_page($GLOBALS['NEXT_PAGE_TEXT'], $GLOBALS['PAGER_URL_LAST']);
        $this -> first_page($GLOBALS['FIRST_PAGE_TEXT'], $GLOBALS['PAGER_URL_LAST']);
        $this -> last_page($GLOBALS['LAST_PAGE_TEXT'], $GLOBALS['PAGER_URL_LAST']);
    }
}
