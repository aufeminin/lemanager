<?php
namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;


class CertificateSubscriber implements EventSubscriberInterface {
	
	public static function getSubscribedEvents()
	{
		return array(
			'issue.success'     => array('onIssueSuccess', 0),
			'renew.success'     => array('onRenewSuccess', 0),
			'renew.terminate'     => array('onRenewTerminated', 0),
		);
	}
	
	public function onIssueSuccess(CertificateEvent $event)
	{
		$certificate = $event->getCertificate();
		$logger = $event->getLogger();
		
		$builder = new ProcessBuilder();
		$builder->setPrefix('/bin/sh')
				->setWorkingDirectory(DATA_DIR)
				->setArguments(array(COPY_SCRIPT, $certificate->getName()));
		
		$builder->getProcess()->run();
		
		$certificate->setInstallStatus($builder->getProcess()->isSuccessful());
		
		$logger->info($builder->getProcess()->getOutput());
		
	}
	
	public function onRenewSuccess(CertificateEvent $event)
	{
		return $this->onIssueSuccess($event);
	}
	
	public function onRenewTerminated(RenewTerminatedEvent $event)
	{
		$renewedCertificates = $event->getRenewedCertificates();
		$emailHandler = $event->getEmailHandler();
		
		if (!empty($renewedCertificates)) {
			$doReload = true;
			
			foreach ($renewedCertificates as $certificate) {
				if (! $certificate->isInstalled()) {
					$doReload = false;
				}
			}
			if ($doReload) {
				$builder = new ProcessBuilder();
				$builder->setPrefix('/bin/sh')
					->setWorkingDirectory(DATA_DIR)
					->setArguments(array(RELOAD_SCRIPT, $certificate->getName()));
				
				$builder->getProcess()->run();
			
				$emailHandler->sendReloadLog($builder->getProcess()->getOutput());
			}
		}
	}
	
}