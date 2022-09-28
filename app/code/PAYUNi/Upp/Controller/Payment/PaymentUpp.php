<?php
namespace PAYUNi\Upp\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
Abstract class PaymentUpp extends Action
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
        // 取得 core_config_data的值
        $this->scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->version     = "1.0";
        $this->test_mode   = $this->_getConfigValue('test_mode');
        $this->MerchantID  = $this->_getConfigValue('merchant_id');
        $this->HashKey     = $this->_getConfigValue('hash_key');
        $this->HashIV      = $this->_getConfigValue('hash_iv');
        // Test Mode
        if ($this->test_mode) {
            $this->gateway = "https://sandbox-api.payuni.com.tw/api/upp"; //測試網址
        } else {
            $this->gateway = "https://api.payuni.com.tw/api/upp"; // 正式網址
        }
    }
    protected function _getConfigValue($key)
    {
        $path = 'payment/payuni/' . $key;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * 加密
     *
     */
    protected function Encrypt($encryptInfo)
    {
        $tag = '';
        $encrypted = openssl_encrypt(http_build_query($encryptInfo), 'aes-256-gcm', trim($this->HashKey), 0, trim($this->HashIV), $tag);
        return trim(bin2hex($encrypted . ':::' . base64_encode($tag)));
    }
    /**
     * 解密
     */
    protected function Decrypt(string $encryptStr = '')
    {
        list($encryptData, $tag) = explode(':::', hex2bin($encryptStr), 2);
        $encryptInfo = openssl_decrypt($encryptData, 'aes-256-gcm', trim($this->HashKey), 0, trim($this->HashIV), base64_decode($tag));
        parse_str($encryptInfo, $encryptArr);
        return $encryptArr;
    }
    /**
     * hash
     */
    protected function HashInfo(string $encryptStr = '')
    {
        return strtoupper(hash('sha256', $this->HashKey.$encryptStr.$this->HashIV));
    }
    /**
     * 處理api回傳的結果
     * @ author    Yifan
     * @ dateTime 2022-08-26
     */
    protected function ResultProcess($result)
    {
        $msg = '';
        if (is_array($result)) {
            $resultArr = $result;
        }
        else {
            $resultArr = json_decode($result, true);
            if (!is_array($resultArr)){
                $msg = 'Result must be an array';
                return ['success' => false, 'message' => $msg];
            }
        }
        if (isset($resultArr['EncryptInfo'])){
            if (isset($resultArr['HashInfo'])){
                $chkHash = $this->HashInfo($resultArr['EncryptInfo']);
                if ( $chkHash != $resultArr['HashInfo'] ) {
                    $msg = 'Hash mismatch';
                    return ['success' => false, 'message' => $msg];
                }
                $resultArr['EncryptInfo'] = $this->Decrypt($resultArr['EncryptInfo']);
                return ['success' => true, 'message' => $resultArr];
            }
            else {
                $msg = 'missing HashInfo';
                return ['success' => false, 'message' => $msg];
            }
        }
        else {
            $msg = 'missing EncryptInfo';
            return ['success' => false, 'message' => $msg];
        }
    }
    protected function SetStatusChange($status, $comment = '', $front = true)
    {
        $this->_order->setState($status, true)
            ->addStatusHistoryComment($comment, $status)
            ->setIsVisibleOnFront($front);
        $this->_order->save();
    }
    /**
     * 產生訊息內容
     * return string
     */
    protected function SetNotice(Array $encryptInfo, String $mini = '1') {
        $trdStatus = ['待付款','已付款','付款失敗','付款取消'];
        $authType  = [1=>'一次', 2=>'分期', 3=>'紅利', 7=>'銀聯'];
        $store     = ['SEVEN' => '統一超商 (7-11)'];
        if ($mini == '2') {
            $message  = "授權狀態：" . $encryptInfo['Message'];
            $message .= " | 訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
            $message .= " | UNi序號：" . $encryptInfo['TradeNo'];
            switch ($encryptInfo['PaymentType']) {
                case '2':
                    $message .= " | 銀行代碼：" . $encryptInfo['BankType'];
                    $message .= " | 繳費帳號：" . $encryptInfo['PayNo'];
                    $message .= " | 待繳金額：" . $encryptInfo['TradeAmt'];
                    $message .= " | 繳費截止時間：" . $encryptInfo['ExpireDate'];
                    break;
                case '3':
                    $message .= " | 繳費方式：" . $store[$encryptInfo['Store']];
                    $message .= " | 繳費代號：" . $encryptInfo['PayNo'];
                    $message .= " | 待繳金額：" . $encryptInfo['TradeAmt'];
                    $message .= " | 繳費截止時間：" . $encryptInfo['ExpireDate'];
                    break;
            }
        }
        else {
            $message   = "<<統一金流 PAYUNi>>";
            switch ($encryptInfo['PaymentType']){
                case '1': // 信用卡
                    $message .= "</br>授權狀態：" . $encryptInfo['Message'];
                    $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                    $message .= "</br>訂單編號：" . $encryptInfo['MerTradeNo'];
                    $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                    $message .= "</br>卡號：" . $encryptInfo['Card6No'] . '******' . $encryptInfo['Card4No'];
                    if ($encryptInfo['CardInst'] > 1) {
                        $message .= "</br>分期數：" . $encryptInfo['CardInst'];
                        $message .= "</br>首期金額：" . $encryptInfo['FirstAmt'];
                        $message .= "</br>每期金額：" . $encryptInfo['EachAmt'];
                    }
                    $message .= "</br>授權碼：" . $encryptInfo['AuthCode'];
                    $message .= "</br>授權銀行代號：" . $encryptInfo['AuthBank'];
                    $message .= "</br>授權銀行：" . $encryptInfo['AuthBankName'];
                    $message .= "</br>授權類型：" . $authType[$encryptInfo['AuthType']];
                    $message .= "</br>授權日期：" . $encryptInfo['AuthDay'];
                    $message .= "</br>授權時間：" . $encryptInfo['AuthTime'];
                    break;
                case '2': // atm轉帳
                    $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                    $message .= "</br>訂單編號：" . $encryptInfo['MerTradeNo'];
                    $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                    $message .= "</br>銀行代碼：" . $encryptInfo['BankType'];
                    $message .= "</br>繳費帳號：" . $encryptInfo['PayNo'];
                    $message .= "</br>待繳金額：" . $encryptInfo['TradeAmt'];
                    $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                    break;
                case '3': // 超商代碼
                    $message .= "</br>訂單狀態：" . $trdStatus[$encryptInfo['TradeStatus']];
                    $message .= "</br>訂單編號：" . $encryptInfo['MerTradeNo'];
                    $message .= "</br>UNi序號：" . $encryptInfo['TradeNo'];
                    $message .= "</br>繳費方式：" . $store[$encryptInfo['Store']];
                    $message .= "</br>繳費代號：" . $encryptInfo['PayNo'];
                    $message .= "</br>待繳金額：" . $encryptInfo['TradeAmt'];
                    $message .= "</br>繳費截止時間：" . $encryptInfo['ExpireDate'];
                    break;
            }
        }
        return $message;
    }
}