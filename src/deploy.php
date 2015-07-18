<?php

use TokenPost\CaPHPistrano;

$caphp = CaPHPistrano::getInstance();

$caphp->task('helloTask','Works',function() use (&$caphp) {
	$caphp->writeln('<comment>Hello task!</comment>');
});

$caphp->start();