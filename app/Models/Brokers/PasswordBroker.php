<?php namespace Models\Brokers;


class PasswordBroker extends Broker
{
    public function findAll($userId)
    {
        return $this->select('SELECT DISTINCT on (p.passwordid) decrypt(passwordtext) as passwordtext,p.passwordid , url , "isFavorite" FROM "Password" p JOIN "Usecase" c on p.usecaseid = c.usecaseid where userid = ?',[$userId]);
    }
    public function findFavorites($userId)
    {
        return $this->select('SELECT DISTINCT on (p.passwordid) decrypt(passwordtext) as passwordtext,p.passwordid , url , c.usecaseid, "isFavorite"  FROM "Password" p JOIN "Usecase" c on p.usecaseid = c.usecaseid  where "isFavorite" = \'1\' AND userid = ? ',[$userId]);
    }
    public function findShare($userId)
    {
        return $this->select('SELECT DISTINCT on (p.passwordid) decrypt1step(p.passwordvalue) as passwordtext,p.passwordid , url  FROM "Userpassword" p join "Password" p2 on p2.passwordid = p.passwordid JOIN "Usecase" c on p2.usecaseid = c.usecaseid JOIN "Userpassword" u on p.passwordid = u.passwordid AND u.userid = ? ',[$userId]);
    }

    public function RemovePassword($id)
    {
        $sql = 'DELETE from "Password" where passwordid = ?';
        $this->query($sql, [
            $id
        ]);
    }

    public function RemovePasswordSh($id, $userId)
    {
        $sql = 'DELETE from "Userpassword" where passwordid = ? and userid = ?';
        $this->query($sql, [
            $id,
            $userId
        ]);
    }

    public function changeStatusOfFavorite($id, $userId)
    {
       $favorite = $this->selectSingle('SELECT "isFavorite" FROM  "Password" where passwordid = ? and userid = ?',[$id,$userId]);
       var_dump($favorite);
       if ($favorite->isFavorite == 1) {
           $sql = 'UPDATE "Password" SET "isFavorite" = 0 WHERE passwordid = ? and userid = ?';
       }else {
           $sql = 'UPDATE "Password" SET "isFavorite" = 1 WHERE passwordid = ? and userid = ?';
       }
       $this->query($sql,[$id,$userId]);

    }

    public function addPassword($password,$link,$userId){
        $useCasesId = $this->getUseCases($link);
        $passwordId = $this-> gen_uuid();
        $this->query('INSERT INTO "Password"(passwordid, userid, passwordtext, usecaseid) values (?,?,encrypt( ?),?)',[
            $passwordId,
            $userId,
            $password,
            $useCasesId
        ]);
    }

    public function updatePassword($password, $link, $passwordId)
    {
        $useCasesId = $this->getUseCases($link);
        $this->query('UPDATE "Password" SET  passwordtext = encrypt(?), usecaseid = ? WHERE passwordid = ?  ',[
            $password,
            $useCasesId,
            $passwordId
        ]);
    }

    private function getUseCases($link)
    {
        $useCase = $this->selectSingle('Select usecaseid FROM "Usecase" where url = ?',[$link]);

        if ($useCase == null){
            $id = $this-> gen_uuid();
            $this->query('INSERT INTO "Usecase"(usecaseid, url) VALUES (?,?)',[
                    $id,
                    $link
                ]);
            return $id;
        }
        return $useCase->usecaseid;
    }

    public function getPasswordFull($passwordId, $userId)
    {
        $password = $this->selectSingle('SELECT decrypt(passwordtext) as passwordtext ,p.passwordid , url as url FROM "Password" p join "Usecase" u on p.usecaseid = u.usecaseid where passwordid = ? AND userid = ? ',[$passwordId, $userId]);
        $Shared =  $this->select('SELECT * FROM "Userpassword" join "User" U on U.user_id = "Userpassword".userid WHERE passwordid = ?',[$passwordId]);
        $password->Links = $Shared ;

        return $password;
    }

    public function addPasswordToUser($username, $passwordId)
    {
        $user = $this->selectSingle('SELECT u.user_id FROM "User" u WHERE U.username = ?', [$username]);
        $password = $this->selectSingle('SELECT decrypt(passwordtext) as passwordtext FROM "Password" where passwordid = ?',[$passwordId]);

        if ($this->userHasPassword($user->user_id, $passwordId)) {
            $this->query('INSERT INTO "Userpassword"(userid, passwordid, passwordvalue) VALUES (?,?,encrypt1step(?))', [
                $user->user_id,
                $passwordId,
                $password->passwordtext
            ]);
        }
    }

    private function userHasPassword($user_id, $passwordId)
    {
        return is_null($this->selectSingle('SELECT * FROM "Userpassword" WHERE passwordid= ? AND userid = ?',[$passwordId,$user_id]));
    }

    public function revokeAccess($passwordId, $userId)
    {
        $this->query('DELETE FROM "Userpassword" WHERE passwordid = ? and userid = ?',[$passwordId,$userId]);
    }


    public function getPasswordForUseCase($Id, $url)
    {
        $passwordObj = $this->selectSingle('SELECT  decrypt(passwordtext) as passwordtext FROM "Password" join "Usecase" U on "Password".usecaseid = U.usecaseid WHERE U.url = ? and userid = ?',[$url,$Id]);
        if (is_null($passwordObj)){
            $passwordObj = $this->selectSingle('SELECT decrypt1step(p.passwordvalue) as passwordtext  FROM "Userpassword" p join "Password" p2 on p2.passwordid = p.passwordid JOIN "Usecase" c on p2.usecaseid = c.usecaseid JOIN "Userpassword" u on p.passwordid = u.passwordid AND u.userid = ? where url = ?',[$Id, $url]);
        }
        if (is_null($passwordObj)){
            return "";
        }
        $password = $passwordObj->passwordtext;
         return $password;
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