<?php

class Transferuj_Transferuj_Block_Form extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('transferuj/transferuj/form.phtml');
  }

  public function getPaymentImageSrc() {
    return 'http://img.transferuj.pl/platnosci-internetowe/transferuj-full-color-449x162.png';
  }
}