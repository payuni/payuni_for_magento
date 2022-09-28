<?php
namespace PAYUNi\Upp\Block;

class ThankYouMessage extends \Magento\Framework\View\Element\Template
{
    public function getThankYouMessage()
    {
        return $this->getRequest()->getParam('message');
    }
}
