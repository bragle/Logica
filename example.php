<?php

$string = <<<HTML
test = 22

[if ! (test > 21)]

	[print test]
	[exit]

[fi]

HTML;

$logikit = new Logikit();

$logikit->run($string);

print $logikit->error;
