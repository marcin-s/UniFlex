<?php

class Transferuj_Transferuj_NotificationController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$order = Mage::getModel('sales/order');
		$order_id = base64_decode($this->getRequest()->getPost('tr_crc'));
		$order->loadByIncrementId($order_id);
		$methodInstance = $order->getPayment()->getMethodInstance();
		$ip = $methodInstance->getAuthIPUrl();

		$ip = explode(',', $ip);
	
		if (in_array($_SERVER['REMOTE_ADDR'], $ip) && !empty($_POST)) {
			$id_sprzedawcy = $_POST ['id'];
			$status_transakcji = $_POST ['tr_status'];
			$id_transakcji = $_POST ['tr_id'];
			$kwota_transakcji = $_POST ['tr_amount'];
			$kwota_zaplacona = $_POST ['tr_paid'];
			$blad = $_POST ['tr_error'];
			$data_transakcji = $_POST ['tr_date'];
			$opis_transackji = $_POST ['tr_desc'];
			$ciag_pomocniczy = $_POST ['tr_crc'];
			$email_klienta = $_POST ['tr_email'];
			$suma_kontrolna = $_POST ['md5sum'];
			// sprawdzenie stanu transakcji
			if ($status_transakcji == 'TRUE' && $blad == 'none') {
				if (!$order->getEmailSent) {
					$order->sendNewOrderEmail();
					$order->setEmailSent(true);
					$this->saveInvoice($order);
				}
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper('transferuj')->__('The payment has been accepted.'));
			} else {
				$order->cancel();
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('transferuj')->__('The order has been canceled.'));
			}
		}
		echo 'TRUE'; // odpowiedz dla serwera o odebraniu danych
		$order->save();
	}

	protected function saveInvoice(Mage_Sales_Model_Order $order) {

		try {
			if (!$order->canInvoice()) {
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
			}

			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

			if (!$invoice->getTotalQty()) {
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
			}

			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			$invoice->register();
			if (!$invoice->getEmailSent) {
				$invoice->sendEmail();
				$invoice->setEmailSent(true);
			}
			$transactionSave = Mage::getModel('core/resource_transaction')
								 ->addObject($invoice)
								 ->addObject($invoice->getOrder());

			$transactionSave->save();
		} catch (Mage_Core_Exception $e) {

		}
	}

}

?>