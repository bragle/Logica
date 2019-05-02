<?php

$string = <<<HTML

test = 22

[print [cat "this " "is " "a " test]]

[if (! (test < 20))]

	[print (test + 1)]

[fi]

[if (!! (test == 21))]

	[print "test"]
	[exit]

[fi]

HTML;

$logica = new Logica();

$logica->run($string);

print $logica->error;
