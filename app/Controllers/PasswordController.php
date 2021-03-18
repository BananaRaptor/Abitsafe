<?php namespace Controllers;

use Models\Brokers\LoginBroker;
use Models\Brokers\PasswordBroker;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Session;

class PasswordController extends Controller
{

    public function initializeRoutes()
    {
        $this->get('/password', 'password');
        $this->get('/removePassword/{id}','removePassword');
        $this->get('/removePasswordSh/{id}','removePasswordSh');
        $this->get('/favoritePassword/{id}','favoritePassword');
        $this->get('/modifyPassword/{id}','modifyPassword');
        $this->post('/modifyPassword/{id}','modifyPasswordExecute');
        $this->get('/modifyPassword/{passwordId}/deleteUser/{userId}','revokeAccess');
        $this->get('/addPassword','addPassword');
        $this->post('/addPassword','addPasswordExecute');
        $this->get('/sharePassword/{id}', 'sharePassword');
        $this->post('/sharePassword/{id}', 'sharePasswordToUser');
    }

    public function password()
    {
        $shared = (new PasswordBroker())->findShare(Session::getInstance()->read("id"));
        $passwords = (new PasswordBroker())->findAll(Session::getInstance()->read("id"));
        return $this->render('password' , ['title' => 'Gestionnaire de mots de passes', 'passwords' => $passwords, 'shared'=>$shared]);
    }

    public function sharePassword($passwordId)
    {
        return $this->render('share' , ['title' => 'Partage de mots de passe','passwordId'=>$passwordId]);
    }

    public function sharePasswordToUser($passwordId)
    {
        $username = $_POST["UserName"];
        if ((new LoginBroker())->checkIfUserExist($username)) {
            (new PasswordBroker())->addPasswordToUser($username, $passwordId);
            return $this->redirect("/password");
        }
        Flash::error("L'utilisateur n'a pas été trouvé");
        return $this->render('share' , ['title' => 'Partage']);
    }

    public function removePassword($id){
        (new PasswordBroker())->RemovePassword($id);
        return $this->redirect("/password");
    }

    public function removePasswordSh($id){
        (new PasswordBroker())->RemovePasswordSh($id,Session::getInstance()->read("id"));
        return $this->redirect("/password");
    }

    public function favoritePassword($id){
        (new PasswordBroker())->changeStatusOfFavorite($id,Session::getInstance()->read("id"));
        return $this->redirect("/password");
    }

    public function addPassword(){
        return $this->render('addPassword' , ['title' => 'ajout de Mots de passes']);
    }

    public function addPasswordExecute(){
        $password = $_POST["password"];
        $link = $_POST["link"];
        if (is_null($password) || !preg_match ('#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $link )){
            Flash::error("Mots de passes vide ou Lien non-valide");
            return $this->redirect("/addPassword");
        }

        (new PasswordBroker())->addPassword($password,$link,Session::getInstance()->read("id"));

        return $this->redirect("/password");


    }

    public function modifyPasswordExecute($passwordId){
        $password = $_POST["password"];
        $link = $_POST["link"];

        if (is_null($password) || !preg_match ('#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $link )){
            Flash::error("Mots de passes vide ou Lien non-valide");
            return $this->redirect("/addPassword");
        }

        (new PasswordBroker())->updatePassword($password,$link,$passwordId);

        return $this->redirect("/password");

    }

    public function modifyPassword($passwordId){
        $password = (new passwordBroker())->getPasswordFull($passwordId,Session::getInstance()->read("id"));
        return $this->render("modifyPassword", ['title' => "Modification de mots de passe", 'password' => $password]);
    }

    public function revokeAccess($passwordId, $userId){
        (new PasswordBroker())->revokeAccess($passwordId,$userId);
        return $this->redirect('/modifyPassword/'.$passwordId);
    }


}