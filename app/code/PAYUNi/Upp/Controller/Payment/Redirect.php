<?php
namespace PAYUNi\Upp\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Result\PageFactory;
class Redirect extends PaymentUpp
{
    public function __construct(Context $context, Session $checkoutSession, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_order = $checkoutSession->getLastRealOrder();
    }
    public function execute()
    {
        //base_url
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $base_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        // 整理要傳給upp的資料陣列
        $encryptInfo = [
            'MerID'      => $this->MerchantID,
            'MerTradeNo' => $this->_order["increment_id"],
            'TradeAmt'   => round($this->_order["grand_total"]),
            'ExpireDate' => date('Y-m-d', strtotime("+7 days")),
            'ReturnURL'  => $base_url . 'payuni/payment/payunireturn',
            "NotifyURL"  => $base_url . 'payuni/payment/notify', //幕後
            'Timestamp'  => time()
        ];
        $parameter['MerID']       = $this->MerchantID;
        $parameter['Version']     = $this->version;
        $parameter['EncryptInfo'] = $this->Encrypt($encryptInfo);
        $parameter['HashInfo']    = $this->HashInfo($parameter['EncryptInfo']);
        // 丟前景給upp
        foreach ($parameter as $key => $value) {
            $payuni_array[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }

        echo '<form id="payuni" name="payuni" action="' . $this->gateway . '" method="post" target="_top">' . implode('', $payuni_array) . '
            </form>'. "<script>document.forms['payuni'].submit();</script>";
        exit;
    }

}