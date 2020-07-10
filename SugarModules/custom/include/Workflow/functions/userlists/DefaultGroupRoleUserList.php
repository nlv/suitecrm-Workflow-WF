<?php
require_once __DIR__.'/RoleUserList.php';

/**
 * Групповые пользователи в роли
 * К функции "Все в роли" добавляется условие так, что возвращаются
 * только групповые пользователи.
 */
class DefaultGroupRoleUserList extends RoleUserList {

    public function getList($bean) {
        $this->additionalWhere = 'users.is_group = 1';
        return parent::getList($bean);
    }

    public function getName() {
        return 'Групповые пользователи в роли';
    }

}
?>
