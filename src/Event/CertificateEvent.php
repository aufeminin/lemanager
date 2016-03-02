<?php
namespace App\Event;

use Monolog\Logger;
use Symfony\Component\EventDispatcher\Event;
use App\Certificate;

class CertificateEvent extends Event {
	
	private $certificate;
	private $logger;
	
	public function __construct(Certificate $certificate, Logger $logger)
	{
		$this->certificate = $certificate;
		$this->logger = $logger;
	}
	
	public function getCertificate()
	{
		return $this->certificate;
	}
	
	public function getLogger()
	{
		return $this->logger;
	}
}