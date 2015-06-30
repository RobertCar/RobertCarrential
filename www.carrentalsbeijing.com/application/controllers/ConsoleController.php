<?php
class ConsoleController extends ControllerAbstract {

	public function init() {
		parent::init();
		$this->setNoRender();
	}

	public function indexAction() {
	}

	public function mailQueueProcessAction() {
		include 'libs/class.phpmailer.php';
		include 'libs/class.smtp.php';
		$config = Application::getConfig()['mail'];
		$items = Model_MailQueue::getUnprocessedItem();
		$processedIds = array();
		foreach ($items as $item) {
			$mail = new phpMailer();
			$mail->XMailer = 'Foxmail 6.1';
			//$mail->SMTPDebug = 3;
			$mail->isSmtp(true);
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = $config['smtp_secure'];
			$mail->Host = $config['smtp_host'];
			$mail->Port = $config['smtp_port'];
			$mail->Username = $config['smtp_user'];
			$mail->Password = $config['smtp_password'];
			$mail->From = $config['smtp_user'];
			$mail->FromName = 'RCR Notify';
			$mail->CharSet = 'UTF-8';
			$mail->isHtml();
			$recipients = explode(',', $item['to']);
			foreach ($recipients as $r) {
				$mail->addAddress(trim($r));
			}
			$mail->Subject = $item['subject'];
			$mail->Body = $item['message'];
			if ($mail->send()) {
				$processedIds[] = $item['id'];
			} else {
				echo $mail->ErrorInfo;
			}
		}
		Model_MailQueue::setItemAsProcessedBatch($processedIds);
	}
}