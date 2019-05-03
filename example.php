<?php

$string = <<<HTML

counter = 0

[if (counter > (10 - 3))]

	[print [cat counter " is higher than " 7]]

[fi]

[if (counter < 10)]

	counter = (counter + 1)

	[jump 3]

[fi]

[print "counter is higher than " [cat "1" 0]]

[exit]

[print "this message will never be printed"]

HTML;

$logica = new Logica();

$logica->run($string);

print $logica->error();
