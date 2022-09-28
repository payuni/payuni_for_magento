<?php
namespace PAYUNi\Upp\Controller\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Psr\Log\LoggerInterface;
class Notify extends PaymentUpp implements CsrfAwareActionInterface
{
    public function __construct(Context $context, LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $postData = $_REQUEST;
        $result = $this->ResultProcess($postData);
        if ($result['success'] == true) {
            $encryptInfo = $result['message']['EncryptInfo'];
            $this->_order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($encryptInfo['MerTradeNo']);
            if (!$this->_order) {
                $msg = "取得訂單失敗，訂單編號：" . $encryptInfo['MerTradeNo'];
                $this->logger->info($msg);
            }
            if ($result['message']['Status'] == 'SUCCESS') {
                $oAmt = round($this->_order["grand_total"]);
                $rAmt = $encryptInfo['TradeAmt'];
                if ($oAmt != $rAmt) {
                    $msg = "結帳金額與訂單金額不一致";
                    $this->SetStatusChange('canceled', $msg);
                }
                $message = $this->SetNotice($encryptInfo, '2');
                $order_status = '';
                switch ($encryptInfo['TradeStatus']) {
                    case '0':
                        $order_status = 'pending_payment';
                        break;
                    case '1':
                        $order_status = 'processing';
                        break;
                }
                $this->SetStatusChange($order_status, $message);
            }
            else {
                $msg = "交易失敗：" . $result['message']['Status'] . "(" . $result['message']['EncryptInfo']['Message'] . ")";
                $this->SetStatusChange('canceled', $msg);
            }
        }
        else {
            $msg = "解密失敗";
            $this->SetStatusChange('canceled', $msg);
        }
    }
}
