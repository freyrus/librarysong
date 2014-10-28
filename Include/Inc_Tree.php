<?php
class Inc_Tree {
    static function showTree ($tree, $root, $query = array(), $childrens = 'childrens') { ?>
        <ul>
        <?php foreach ($tree as $k => $item) {
            $temp = $query;
            if ($root === NULL) {
                $newRootId = $k;
            } else {
                $newRootId = $root;
                $temp[] = $k;
            }
        ?>
        <li>
            <a onclick="doAction(this, 'updateNode')" href="javascript:void(0)" data-query="<?php echo implode('|', $temp) ?>" data-root="<?php echo $newRootId ?>" data-id="<?php echo (string) $item['_id'] ?>"><?php echo $item['name'] ?></a>
            <?php
            if (empty($item[$childrens]) === FALSE) {
                Inc_Tree::showTree($item[$childrens], $newRootId, $temp);
            }
            ?>
        </li>
        <?php } ?>
        </ul><?php
    }
}
