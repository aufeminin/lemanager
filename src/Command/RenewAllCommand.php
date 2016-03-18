<?php

namespace App\Command;

use App\CertificateHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Event\RenewTerminatedEvent;
use App\Event\CertificateEvent;

class RenewAllCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renew:all')
            ->setDescription('Renew all certificates if it is possible');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ch = new CertificateHandler();
        $ah = $this->getEmailAlertHandler();

        $renewedCertificates = [];

        foreach($ch->getAll() as $certificate) {

            if(!$certificate->isExpiringOrInvalid()) {
                continue;
            }

            $logger = $this->getLogger($certificate);
            $le = $this->getLescript($logger);

            try {
                $le->initAccount();
                $le->signDomains($certificate->getAllDomains(), true);
                $renewedCertificates[] = $certificate;
                
                $event = new CertificateEvent($certificate, $logger);
                $this->dispatcher->dispatch('renew.success', $event);

                $ah->sendRenewLog($certificate);

            } catch(\Exception $e) {

                $logger->error($e->getMessage());
                foreach(explode("\n", $e->getTraceAsString()) as $line) {
                    $logger->debug($line);
                }

                $ah->sendErrorLog($certificate);
            }
        }
        
        $event = new RenewTerminatedEvent($renewedCertificates, $ah);
        $this->dispatcher->dispatch('renew.terminate', $event);
    }
}
