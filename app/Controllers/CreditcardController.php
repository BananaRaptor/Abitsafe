<?php namespace Controllers;

use Models\Brokers\CreditCardBroker;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;

class CreditcardController extends Controller
{
    public function initializeRoutes()
    {
        $this->get('/creditcard', 'creditCard');
        $this->get('/addCreditcard', 'addCreditCard');
        $this->post('/addCreditcard', 'addCreditCardExecute');
        $this->get('/removeCreditcard/{id}', 'removeCreditCard');
        $this->get('/favoriteCreditcard/{id}', 'favoriteCreditCard');
        $this->post('/modifyCreditcard/{id}','modifyCreditCardExecute');
        $this->get('/shareCreditcard/{id}', 'shareCreditCard');
        $this->get('/modifyCreditcard/{id}', 'modifyCreditcard');

    }

    public function creditCard()
    {
        $creditCards = (new \Models\Brokers\CreditCardBroker())->findAll(Session::getInstance()->read("id"));
        return $this->render('creditCard', ['title' => 'Gestionnaire de carte de crédits', 'CreditCards' => $creditCards]);
    }

    public function addCreditCard()
    {
        return $this->render('addCreditCard', ['title' => "Ajout d'une nouvelle carte de crédit"]);
    }

    public function addCreditCardExecute()
    {
        $form = $this->buildForm();
        $form->validate('number', Rule::integer("Votre carte de crédit n'est pas valide"));
        $form->validate('dateExp', Rule::notEmpty("Votre date d'expiration n'est pas valide"));
        $form->validate('cvc', Rule::integer("Votre code cvc n'est pas valide"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect('/addCreditcard');
        }

        $CreditCard = $form->buildObject();

        if (!preg_match('^(?:4[0-9]{12}(?:[0-9]{3})?|[25][1-7][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})^', $CreditCard->number)) {
            Flash::error("Votre carte de crédit n'est pas valide");
            return $this->redirect('/addCreditcard');
        }
        if (strlen($CreditCard->cvc) !=3){
            Flash::error("Votre cvc n'est pas valide");
            return $this->redirect('/addCreditcard');
        }



        (new CreditCardBroker())->addCreditCard($CreditCard->number, $CreditCard->dateExp, $CreditCard->cvc, Session::getInstance()->read("id"));

        return $this->redirect("/creditcard");
    }

    public function removeCreditCard($CreditCardId)
    {

        (new CreditCardBroker())->DeleteCreditCard($CreditCardId);
        return $this->redirect("/creditcard");
    }

    public function favoriteCreditCard($CreditCardId)
    {
        (new CreditCardBroker())->changeStatusOfFavorite($CreditCardId);
        return $this->redirect("/creditcard");
    }

    public function modifyCreditcard($CreditCardId)
    {
        $CreditCard = (new CreditCardBroker())->findDetail($CreditCardId);
        return $this->render("modifyCreditCard", ['title' => 'carte de crédits', 'creditcard' => $CreditCard]);
    }
    public function modifyCreditCardExecute($CreditCardId)
    {

        $form = $this->buildForm();
        $form->validate('number', Rule::integer("Votre carte de crédit n'est pas valide"));
        $form->validate('dateExp', Rule::notEmpty("Votre date d'expiration n'est pas valide"));
        $form->validate('cvc', Rule::integer("Votre code cvc n'est pas valide"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect('/modifyCreditCard');
        }

        $CreditCard = $form->buildObject();

        if (!preg_match('^(?:4[0-9]{12}(?:[0-9]{3})?|[25][1-7][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})^', $CreditCard->number)) {
            Flash::error("Votre carte de crédit n'est pas valie");
            return $this->redirect('/modifyCreditCard');
        }

        if (strlen($CreditCard->cvc) !=3){
            Flash::error("Votre cvc n'est pas valide");
            return $this->redirect('/modifyCreditCard');
        }


        (new CreditCardBroker())->modifyCreditCard($CreditCard->number, $CreditCard->dateExp, $CreditCard->cvc, $CreditCardId);
        return $this->redirect("/creditcard");

    }
}
