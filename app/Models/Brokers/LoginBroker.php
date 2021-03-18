<?php namespace Models\Brokers;

use stdClass;
use Zephyrus\Security\Cryptography;

class LoginBroker extends Broker
{

    public function insert(stdClass $user)
    {
        $passwordHased =  Cryptography::hashPassword($user->password);
        $sql = 'INSERT INTO "User"( user_id, username, email, lastname, firstname, loginpassword) VALUES (?, ? , ? , ?, ?, ?)';
        $this->query($sql, [
            $this-> gen_uuid(),
            $user->username,
            $user-> email,
            $user-> lastName,
            $user-> firstName,
            $passwordHased
        ]);
    }
    public function verify(string $username, string $password) : ?stdClass
    {
        $sql = 'SELECT * FROM "User" WHERE username = ?';
        $user =  $this->selectSingle($sql, [$username]);
        if (is_null($user)){
            return null;
        }
        //printf(Cryptography::hashPassword($password));

        if (!Cryptography::verifyHashedPassword($password, $user->loginpassword)){
            return null;
        }
        return $user;
    }

    public function checkIfUserExist($username)
    {

        $sql = 'SELECT * FROM "User" WHERE username = ?';
        $user =  $this->selectSingle($sql, [$username]);
        if (is_null($user)){
            return false;
        }
        return true;
    }

    public function getEmail($userId)
    {
        return $this->selectSingle('SELECT email from "User" where user_id = ?',[$userId])->email;
    }

    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}