<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../web/_config.php';

use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Event\CertificateSubscriber;

$console = new Application('LEManager', 'Beta');

$dispatcher = new EventDispatcher();

if (USE_CALLBACK === true) {
	$certificateSubscriber = new CertificateSubscriber();
	$dispatcher->addSubscriber($certificateSubscriber);
}

$console->add(new App\Command\RenewAllCommand($dispatcher));
$console->add(new App\Command\IssueNewCommand($dispatcher));

$console->run();
