<?php

$string = <<<HTML
test = 22

[if ! (test > 21)]

	[print test]
	[exit]

[fi]

HTML;

$logica = new Logica();

$logica->run($string);

print $logica->error;
