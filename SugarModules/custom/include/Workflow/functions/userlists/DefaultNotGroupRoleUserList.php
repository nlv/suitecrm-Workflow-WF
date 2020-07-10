<?php
require_once __DIR__.'/RoleUserList.php';

/**
 * Не групповые пользователи в роли
 * К функции "Все в роли" добавляется условие так, что возвращаются
 * только групповые пользователи.
 */
class DefaultNotGroupRoleUserList extends RoleUserList {

    public function getList($bean) {
        $this->additionalWhere = 'users.is_group = 0';
        return parent::getList($bean);
    }

    public function getName() {
        return 'Не групповые пользователи в роли';
    }

}
?>
