<?php

namespace Notch;

class Users extends Base
{
    public function getUserById($userId)
    {
        return $this->getDb()->fetchOne('select * from users where id = '.$userId);
    }

    public function getUserByUsername($username)
    {
        return $this->getDb()->fetchOne('select * from users where username = "'.$username.'"');
    }

    public function login($username, $password)
    {
        $sql = 'select * from users where username = "'.$username.'" and password = "'.$password.'"';
        $result = $this->getDb()->fetchOne($sql);
        return (empty($result)) ? false : true;
    }

    public function create($data)
    {
        $sql = 'insert into users (username, password, email, created, updated)'
            .' values ("'.$data['username'].'", "'.$data['password'].'",'
            .' "'.$data['email'].'", now(), now())';

        return $this->getDb()->execute($sql);
    }

    public function save($data)
    {
        $sql = 'update users set password = "'.$data['password'].'", email = "'.$data['email'].'"'
            .' where id = '.$data['id'];
        echo $sql;

        return $this->getDb()->execute($sql);
    }
}