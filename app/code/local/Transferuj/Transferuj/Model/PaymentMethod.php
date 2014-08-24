<?php

class Transferuj_Transferuj_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {

  protected $_code          = 'transferuj';
  protected $_formBlockType = 'transferuj/form';
  protected $_canUseInternal=false;
  protected $_canUseCheckout=false;
  protected $_order;

public function __construct()
{
	if(!in_array("PLN", Mage::app()->getStore()->getAvailableCurrencyCodes(true)))
	{
		  $this->_canUseInternal=false;
		  $this->_canUseCheckout=false;
	} else
	{
		  $this->_canUseInternal=true;
		  $this->_canUseCheckout=true;
	}
}
  public function getOrder() {
    if (!$this->_order)
      $this->_order = $this->getInfoInstance()->getOrder();
    return $this->_order;
  }

  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl('transferuj/processing/redirect');
  }

  public function getRedirectUrl() {
    return $this->getConfigData('redirect_url');
  }

   public function getAuthIPUrl() {
    return $this->getConfigData('tran_ip');
  }

  public function getRedirectionFormData() {
  
    $billing = $this->getOrder()->getBillingAddress();
	$order_id = $this->getOrder()->getRealOrderId();
	$crc=base64_encode($order_id);
	$amount = round($this->getOrder()->getGrandTotal(), 2);
    $md5sum=md5($this->getConfigData('id').$amount.$crc.$this->getConfigData('kodp'));
	
    return array(
      'id'          => $this->getConfigData('id'),
      'kwota'      => $amount,
      'opis' => Mage::helper('transferuj')->__('Zamówienie: %s', $this->getOrder()->getRealOrderId()),
      'email'       => $billing->getEmail() ? $billing->getEmail() : $this->getOrder()->getCustomerEmail(),
      'imie'   => $billing->getFirstname(),
      'nazwisko'    => $billing->getLastname(),
	  'crc'=>$crc,
	  'md5sum'=>$md5sum,	  
      'pow_url'         => Mage::getUrl('checkout/onepage/success/'),
	  'pow_url_blad'         => Mage::getUrl('customer/account/'),
      'wyn_url'        => Mage::getUrl('transferuj/notification'),
      'kraj'     => $billing->getCountryModel()->getIso2Code(),
      'jezyk'     => Mage::app()->getLocale()->getLocaleCode(),
      'miasto'        => $billing->getCity(),
      'kod'    => $billing->getPostcode(),
      'adres'      => $billing->getStreet(-1),
      'telefon'       => $billing->getTelephone(),
    );
  }
}