<?php
namespace PAYUNi\Upp\Model;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
	protected $_code = 'payuni';
	protected $_isInitializeNeeded = true;
    protected $_canCapture = true;
    protected $_canAuthorize = true;
    // protected $_canRefund  = true;
    protected $_canVoid = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isGateway = true;
    protected $_canUseInternal = false;

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState("pending_payment");
        $stateObject->setStatus("pending_payment");
        $stateObject->setIsNotified(false);
    }
}