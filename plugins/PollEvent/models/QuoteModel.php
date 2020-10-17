<?php


class QuoteModel extends Gdn_Model {
    public function __construct() {
        parent::__construct('Quote');
    }

    public function getAll(){
        $this->SQL
            ->select('q.*')
            ->from('Quote q')
            ->orderBy('q.date');

        return $this->SQL->get();
    }

    public function save($formPostValues, $settings = false) {
        $this->add($formPostValues['text'], $formPostValues['name']);
    }

    public function add($text, $name){
        $now = new DateTime('now');
        $this->SQL
            ->Insert('Quote', array(
                    'text' => $text,
                    'name' => $name,
                    'date' => $now->format("Y-m-d H:i:s"))
            );
    }
}
