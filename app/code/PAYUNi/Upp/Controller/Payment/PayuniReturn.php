<?php
namespace PAYUNi\Upp\Controller\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
class PayuniReturn extends PaymentUpp implements CsrfAwareActionInterface
{
    public function __construct(Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
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
        try {
            $result = $this->ResultProcess($_REQUEST);
            if ($result['success'] == true) {
                if ($result['message']['Status'] == 'SUCCESS') {
                    $encryptInfo = $result['message']['EncryptInfo'];
                    $this->_order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($encryptInfo['MerTradeNo']);
                    if (!$this->_order) {
                        $msg = "取得訂單失敗，訂單編號：" . $encryptInfo['MerTradeNo'];
                        throw new \Exception($msg);
                    }
                    $oAmt = round($this->_order["grand_total"]);
                    $rAmt = $encryptInfo['TradeAmt'];
                    if ($oAmt != $rAmt) {
                        $msg = "結帳金額與訂單金額不一致";
                        throw new \Exception($msg);
                    }
                    $message = $this->SetNotice($encryptInfo);
                    $this->_forward('Message','Payment','payuni', ['message'=>$message]);
                }
                else {
                    $msg = "交易失敗：" . $result['message']['Status'] . "(" . $result['message']['EncryptInfo']['Message'] . ")";
                    throw new \Exception($msg);
                }
            }
            else {
                $msg = "解密失敗";
                throw new \Exception($msg);
            }
        }
        catch (\Exception $e) {
            $this->_forward('Message','Payment','payuni', ['message'=>$e->getMessage()]);
        }

    }
}
