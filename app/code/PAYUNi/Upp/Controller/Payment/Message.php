<?php

namespace PAYUNi\Upp\Controller\Payment;

class Message extends \Magento\Framework\App\Action\Action {

	protected $resultPageFactory;

	public function __construct(\Magento\Framework\App\Action\Context $context,
                              \Magento\Framework\View\Result\PageFactory $resultPageFactory)
  {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
  }

	/**
	 * Sets the content of the response
	 */
	public function execute() {
		return $this->resultPageFactory->create();
	}

}
