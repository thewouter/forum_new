<?php


class QuoteController extends VanillaController{
    /** @var Gdn_Form */
    public $Form;

    public function initialize() {
        $this->Form = new Gdn_Form();
        parent::initialize();
    }

    public function index(){
        $quoteModel = new QuoteModel();
        $this->Form->setModel($quoteModel);
        if ($this->Form->authenticatedPostBack()){
            $this->Form->save();
            redirect('/quote');
        } else {

            $this->View = 'index';
            $quotes = $quoteModel->getAll()->result();
            function compare_quotes($a, $b) { // Make sure to give this a more meaningful name!
                return strtotime($b->date) - strtotime($a->date);
            }

            usort($quotes, 'compare_quotes');
            $this->setData('quotes', $quotes);

        }
        $this->render();
    }

}
