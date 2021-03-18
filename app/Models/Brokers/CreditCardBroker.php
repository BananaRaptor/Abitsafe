<?php namespace Models\Brokers;

class CreditCardBroker extends Broker
{

    public function findAll($userId)
    {
        return $this->select('SELECT DISTINCT on (DECRYPT(c."CreditCardNumber")) decrypt(c."CreditCardNumber") as "CreditCardNumber", "Provider","isFavorite","ExpirationDate","Id" FROM "CreditCard" c  where "UserId" = ?',[$userId]);
    }
    public function findFavorites($userId)
    {
        return $this->select('SELECT DISTINCT on (DECRYPT(c."CreditCardNumber")) decrypt(c."CreditCardNumber") as "CreditCardNumber", "Provider","isFavorite","ExpirationDate","Id"  FROM "CreditCard" c  where "UserId" = ? AND "isFavorite" = 1',[$userId]);
    }

    public function addCreditCard($number, $dateExp, $cvc, $userId)
    {
        if ($number[0] == 4) {
            $provider = "Visa";
        }else {
            $provider = "MasterCard";
        }
        $sql = 'INSERT INTO "CreditCard"("Id","CreditCardNumber", "ExpirationDate", cvc, "UserId", "isFavorite", "Provider") values (?,encrypt(?),?,?,?,?,?) ';
        $this->query($sql,[
            $this->gen_uuid(),
            $number,
            $dateExp,
            $cvc,
            $userId,
            1,
            $provider
        ]);

    }

    public function changeStatusOfFavorite($Id)
    {
        $favorite = $this->selectSingle('SELECT * FROM "CreditCard" where "Id" = ?',[$Id]);
        var_dump($favorite);
        if ($favorite->isFavorite == 1) {
            printf("aaaa");
            $sql = 'UPDATE "CreditCard" SET "isFavorite" = 0 WHERE "Id" = ?';
        }else {
            printf("hein");
            $sql = 'UPDATE "CreditCard" SET "isFavorite" = 1 WHERE "Id" = ?';
        }
        $this->query($sql,[$Id]);

    }

    public function DeleteCreditCard($Id)
    {
        $sql = 'DELETE FROM "CreditCard" WHERE "Id" = ? ';
        $this->query($sql,[
            $Id,
        ]);
    }

    public function findDetail($Id)
    {
        return $this->selectSingle('SELECT decrypt("CreditCardNumber") as "CreditCardNumber", "Provider","isFavorite","ExpirationDate","Id",cvc FROM "CreditCard" WHERE "Id" = ?',[$Id]);
    }

    public function modifyCreditCard($number, $dateExp, $cvc, $CreditCardId)
    {
        if ($number[0] == 4) {
            $provider = "Visa";
        }else {
            $provider = "MasterCard";
        }
        $this->query('UPDATE "CreditCard" SET "CreditCardNumber" = encrypt(?), "ExpirationDate" = ? , cvc = ? , "Provider" = ? WHERE "Id" = ?',[
                $number,
                $dateExp,
                $cvc,
                $provider,
                $CreditCardId
            ]
        );
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