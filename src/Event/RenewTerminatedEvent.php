<?php
namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use App\EmailAlertHandler;

class RenewTerminatedEvent extends Event {
	
	private $renewedCertificates;
	private $emailHandler;
	
	public function __construct($renewedCertificates, EmailAlertHandler $emailHandler) {
		$this->renewedCertificates = $renewedCertificates;
		$this->emailHandler = $emailHandler;
	}
	
	public function getRenewedCertificates() {
		return $this->renewedCertificates;	
	}
	
	public function getEmailHandler() {
		return $this->emailHandler;
	}
}