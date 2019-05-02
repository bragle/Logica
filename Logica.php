<?php

class Logica {

	private $vars = [];
	private $parts = [];

	public $function = [];
	public $operator = [];

	public $error = false;
	public $terminate = false;

	private $length = 0;
	private $line = 0;

	public function __construct(){

		$this->function = [

			'if' => function($stack){

				if(!$this->test($stack)){

					while(!$this->terminate && $this->line < $this->length && $this->line < 200){

						if($this->part() == '[fi]'){

							break;

						}

						$this->line++;

					}

				}

			},

			'printvar' => function($stack){

				echo $this->getVar($stack);

			},

			'print' => function($stack){

				echo implode(' ', $stack);

			},

			'exit' => function($stack){

				$this->terminate = true;

			}

		];

		$this->operator = [

			'==' => function ($a, $b) { return $a == $b; },
			'===' => function ($a, $b) { return $a === $b; },
			'!=' => function ($a, $b) { return $a != $b; },
			'!==' => function ($a, $b) { return $a !== $b; },
			'>' => function ($a, $b) { return $a > $b; },
			'>=' => function ($a, $b) { return $a >= $b; },
			'<' => function ($a, $b) { return $a < $b; },
			'<=' => function ($a, $b) { return $a <= $b; },
			'%' => function ($a, $b) { return $a % $b; },
			'!!' => function ($a) { return !!$a; },
			'!' => function ($a) { return !$a; }

		];

	}

	private function setVar(){

		$parts = explode(' ', $this->part(), 3);

		if(ctype_alpha($parts[0]) && !isset($this->function[$parts[0]])){

			if(count($parts) === 3 && $parts[1] == '='){

				if(strpos($parts[2], ' ') !== false){

					$parts[2] = $this->test($parts[2]);

				}

				$this->vars[$parts[0]] = $parts[2];

				return true;

			}

		}

		return false;

	}

	private function getVar($var){

		if(isset($this->vars[$var])){

			return $this->vars[$var];

		}else if(ctype_digit($var)){

			return (int) $var;

		}else if(is_bool($var)){
			
			return $var;
			
		}else{

			$this->terminate = true;

			return null;

		}

	}

	private function part(){

		return trim($this->parts[$this->line]);

	}

	private function test($stack){

		foreach($stack as &$param){

			if(substr($param, 0, 1) === '('){

				$param = $this->test(explode(' ', substr($param, 1, -1)));

			}

		}

		switch(count($stack)){

			case 3:

				return $this->operator[$stack[1]]($this->getVar($stack[0]), $this->getVar($stack[2]));

				break;

			case 2:

				return $this->operator[$stack[0]]($this->getVar($stack[1]));

				break;

			case 1:

				return !! $this->getVar($stack[0]);

				break;

		}

	}

	private function execute($string){

		if(substr($string, -1) !== ']' && substr($string, -1) !== ')'){

			$this->error = 'Function call not ended';
			$this->terminate = true;

			return false;

		}

		preg_match_all('/\(.*?\)|\[.*?\]|[^ ]+/', substr($string, 1, -1), $stack); // split on all spaces that are not brackets

		$stack = $stack[0];

		$function = array_shift($stack);

		foreach($stack as &$param){

			if(substr($param, 0, 1) === '['){

				$param = $this->execute($param);

			}else if(isset($this->vars[$param])){

				$param = $this->vars[$param];

			}

		}

		if(!isset($this->function[$function])){

			$this->error = 'Function does not exist';
			$this->terminate = true;

			return false;

		}

		return $this->function[$function]($stack);

	}

	public function run($string){

		$this->parts = explode("\n", $string);

		$this->length = count($this->parts);

		$this->line = 0;

		while(!$this->terminate && $this->line < $this->length && $this->line < 200){

			echo "\n" . $this->line . ' | ' . $this->part() . ' | ';

			if($this->part()){

				if(substr($this->part(), 0, 1) === '['){

					$this->execute($this->part());

				}else if(!$this->setVar()){

					$this->error = 'Unable to set variable';
					$this->terminate = true;

				}

			}

			$this->line++;

		}

	}

}
