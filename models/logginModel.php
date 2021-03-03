<?php

class LogginModel
{
    function __construct()
    {
        $this->db = Database::getPDO();
    }

    function compare(string $userEmail, string $password)
    {
        try{
            $user = $this->db->query("SELECT * FROM user WHERE email='" . $userEmail . "'")->fetch();
        }
        catch (PDOException $e) {
            setcookie('error', $e->getMessage());
            header('Location: ' . BASE_PATH . "error");
        }

        if (isset($user)) {

            $isPasswordCorrect = password_verify($password, $user['password']);

            if ($isPasswordCorrect) {
                setcookie("userId", $user["userId"], time() + 600, '/');
                return true;
            } else {
                return false;
            }
        } else {

            return false;
        }
    }

    function out(): bool
    {
        unset($_COOKIE['userId']);
        if(isset($_COOKIE['userId'])){
            return false;
        }else{
            return true;
        }
    }

    function signIn($params){
        //toDo
    }

    function signOut($id){
        //toDo
    }
}

