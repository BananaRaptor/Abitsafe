<?php namespace Controllers;

use Models\Brokers\CreditCardBroker;
use Models\Brokers\LoginBroker;
use Models\Brokers\PasswordBroker;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;
use Zephyrus\Security\Cryptography;

class AbitsafeController extends Controller
{
    public function initializeRoutes()
    {
        $this->get('/', 'reRouteToHome');
        $this->get('/index', 'index');
        $this->get('/login', 'login');
        $this->post('/login', 'loginUser');
        $this->get('/createUser', 'createUser');
        $this->post('/createUser', 'createUserDB');
        $this->get('/deco','deco');
        $this->post('/api', 'extensionApi');
        $this->post('/apiLogin','extensionLogin');

    }

    public function deco(){
        Session::getInstance()->destroy();
        return $this->redirect('/login');
    }

    public function reRouteToHome(){
        return $this->redirect("index");
    }
    public function index()
    {
        $passwords = (new PasswordBroker())->findFavorites(Session::getInstance()->read("id"));
        $creditCards = (new CreditCardBroker())->findFavorites(Session::getInstance()->read("id"));
        return $this->render('index', ['title' => 'Accueil','passwords' => $passwords,'CreditCards' => $creditCards]);
    }


    public function login()
    {
        return $this->render('login' , ['title' => 'login']);
    }
    public function createUser()
    {
        return $this->render('createUser' , ['title' => 'Créer un compte']);
    }
    public function createUserDB ()
    {
        $form = $this->buildForm();
        $form->validate('username', Rule::alphanumeric("Le nom d'utilisateur ne doit pas etre vide"));
        $form->validate('email', Rule::email("Le email doit être valide"));
        $form->validate('firstName', Rule::alphanumeric("Le prénom ne doit pas être vide"));
        $form->validate('lastName', Rule::alphanumeric("Le nom de famille ne doit pas être vide"));
        $form->validate('password', Rule::notEmpty("Le mots de passe ne doit pas être vide"));
        if (! $form->verify()){
            Flash::error($form->getErrorMessages());
            return $this->redirect('/createUser');
        }

        (new LoginBroker())->insert($form->buildObject());
        Flash::success("Votre compte a bel et bien été créer");
        return  $this->redirect("/login");
    }

    public function loginUser ()
    {
        $username = $_POST["username"];
        $password = $_POST["password"];
        Session::getInstance()->set('UserCryptKey',Cryptography::deriveEncryptionKey( $password, VALIDATOR));
        $result = (new LoginBroker())->verify($username, $password);

        if ($result == null) {
            Flash::success("erreur lors de votre authentification");
            return $this->render('login' , ['title' => 'login']);
        }
        Session::getInstance()->set('id', $result->user_id);
        return $this->redirect("/index");
    }

    public function extensionApi()
    {
        $url = $_POST["url"];
        $password = (new PasswordBroker())->getPasswordForUseCase(Session::getInstance()->read("id"),$url);
        $email = (NEW LoginBroker())->getEmail(Session::getInstance()->read("id"));
        return $this->json('{"password": "'.$password.'", "email": "'.$email.'"}');
    }

    public function extensionLogin(){
        $username = $_POST["username"];
        $password = $_POST["password"];
        Session::getInstance()->set('UserCryptKey',Cryptography::deriveEncryptionKey( $password, VALIDATOR));
        $result = (new LoginBroker())->verify($username, $password);

        if ($result == null) {
            Flash::success("erreur lors de votre authentification");
            return  $this->json('{"loginStatus" : "false"}');
        }
        Session::getInstance()->set('id', $result->user_id);
        return  $this->json('{"loginStatus" : "true", "sid" :"'.Session::getInstance()->getId().'"}');
    }
}