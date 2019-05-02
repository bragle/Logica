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

				if(!$this->return($stack[0])){

					while(!$this->terminate && $this->line < $this->length && $this->line < 200){

						if($this->part() == '[fi]'){

							break;

						}

						$this->line++;

					}

				}

			},

			'fi' => function(){},

			'cat' => function($stack){

				return implode('', $stack);

			},

			'request' => function($stack){

				$request = $stack[0];

				echo 'sending request to interwebz: ' . $request . "\n";

			},

			'print' => function($stack){

				echo implode(' ', $stack) . "\n";

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
			'!' => function ($a) { return !$a; },
			'+' => function ($a, $b) { return $a + $b; }

		];

	}

	private function split($string){

		$inString = false;

		$depth = 0;
		$split = 0;
		$length = strlen($string) - 1;

		$array = [];

		foreach(str_split($string) as $i => $char){

			if($char === '"'){

				$inString = !$inString;

			}

			if(!$inString){

				if(in_array($char, ['(', '['])){

					$depth++;

				}else if(in_array($char, [')', ']'])){

					$depth--;

				}

				if((ctype_space($char) || $i == $length) && !$depth){

					// echo "\n  | " . substr($string, $split, $i == $length ? $length + 1 : $i - $split); // for debugging

					$array[] = substr($string, $split, $i == $length ? $length + 1 : $i - $split);

					$split = $i + 1;

				}

			}

		}

		return $array;

	}

	private function part(){

		return trim($this->parts[$this->line]);

	}

	private function setVar(){

		$parts = explode(' ', $this->part(), 3);

		if(ctype_alpha($parts[0]) && !isset($this->function[$parts[0]]) && count($parts) === 3 && $parts[1] == '='){

			$this->vars[$parts[0]] = $this->return($parts[2]);

			return true;

		}

		$this->error = 'Attempted to set illegal var';
		$this->terminate = true;

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

	private function execute($string){

		$stack = $this->split(substr($string, 1, -1));

		$function = array_shift($stack);

		foreach($stack as &$param){

			$param = $this->return($param);

		}

		if(!isset($this->function[$function])){

			$this->error = 'Function does not exist';
			$this->terminate = true;

			return false;

		}

		return $this->function[$function]($stack);

	}

	private function test($string){

		$stack = $this->split(substr($string, 1, -1));

		foreach($stack as &$param){

			$param = $this->return($param);

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

	private function return($string){

		if(is_array($string)){

			$this->error = 'Illegal parameter';
			$this->terminate = true;

			return false;

		}

		if(substr($string, 0, 1) === '"' || substr($string, -1) === '"'){

			return substr($string, 1, -1);

		}else if(substr($string, 0, 1) === '[' || substr($string, -1) === ']'){

			return $this->execute($string);

		}else if(substr($string, 0, 1) === '(' || substr($string, -1) === ')'){

			return $this->test($string);

		}else if(isset($this->vars[$string])){

			return $this->vars[$string];

		}

		return $string;

	}

	public function run($string){

		$this->parts = explode("\n", $string);

		$this->length = count($this->parts);

		$this->line = 0;

		while(!$this->terminate && $this->line < $this->length && $this->line < 200){

			// echo "\n { " . $this->line . ' | ' . $this->part() . ' } '; // for debugging

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
