<?php if (!defined('APPLICATION')) exit();

class PollModule extends Gdn_Module {
    public static $ApplicationFolder = 'plugins/PollEvent';

    public function __construct($Sender = '') {
        parent::__construct($Sender, self::$ApplicationFolder);
    }

    public function getData($PollString) {
        $this->setData('PollString', $PollString);
    }

    public function assetTarget() {
        return '';
    }

    public function toString() {
        return parent::ToString();
    }
}
