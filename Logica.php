<?php

class Logica {

	private $vars = [];
	private $parts = [];

	private $function;
	private $operator;
	private $delimiterMap;
	private $delimiterMapReverse;

	private $error = false;
	private $terminate = false;

	private $length = 0;
	private $steps = 0;
	private $line = 0;

	private $maxSteps = 200;

	public function __construct(){

		$this->function = [

			'if' => function($stack){

				if(count($stack) !== 1){

					$this->error = 'if function requires one param';
					$this->terminate = true;

					return false;

				}

				if(!$this->return($stack[0])){

					while($this->line < $this->length - 1){

						if($this->part() == '[fi]'){

							return true;

						}

						$this->line++;

					}

				}

				return false;

			},

			'fi' => function(){},

			'jump' => function($stack){

				if(count($stack) !== 1){

					$this->error = 'jump function requires one param';
					$this->terminate = true;

					return false;

				}

				if(!ctype_digit($stack[0])){

					return false;

				}

				$this->line = $stack[0] - 1;

				return true;

			},

			'cat' => function($stack){

				return implode('', $stack);

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
			'+' => function ($a, $b) { return $a + $b; },
			'-' => function ($a, $b) { return $a - $b; },
			'||' => function ($a, $b) { return $a || $b; },
			'&&' => function ($a, $b) { return $a && $b; },

			'!!' => function ($a) { return !!$a; },
			'!' => function ($a) { return !$a; }

		];

		$this->delimiterMap = [

			'[' => ']',
			'(' => ')'
			
		];

		$this->delimiterMapReverse = array_flip($this->delimiterMap);

	}

	private function split($string){

		$inString = false;

		$delimiterStack = [];

		$length = strlen($string) - 1;
		$split = 0;

		$array = [];

		foreach(str_split($string) as $i => $char){

			if($char === '"'){

				$inString = !$inString;

			}

			if(!$inString){

				if(isset($this->delimiterMap[$char])){

					$delimiterStack[] = $char;

				}else if(isset($this->delimiterMapReverse[$char])){

					if(end($delimiterStack) != $this->delimiterMapReverse[$char]){

						$this->error = 'Function or operation not closed properly';
						$this->terminate = true;

						return false;

					}

					array_pop($delimiterStack);

				}

				if((ctype_space($char) || $i == $length) && !$delimiterStack){

					$array[] = substr($string, $split, $i == $length ? $length + 1 : $i - $split);

					$split = $i + 1;

				}

			}

		}

		return $array;

	}

	private function validateCall($function, $paramCount){

		if(!is_callable($function)){

			$this->error = 'Uncallable object passed as function';
			$this->terminate = true;

			return false;

		}

		if((new ReflectionFunction($function))->getNumberOfRequiredParameters() !== $paramCount){

			$this->error = 'Function or operation call has invalid parameter count';
			$this->terminate = true;

			return false;

		}

		return true;

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

		}else if(is_numeric($var)){

			return (int) $var;

		}else if(is_bool($var)){
			
			return $var;
			
		}else{

			$this->error = 'Attempted to get undefined var';
			$this->terminate = true;

			return null;

		}

	}

	private function execute($string){

		if(!$stack = $this->split(substr($string, 1, -1))){

			return false;

		}

		$function = array_shift($stack);

		if(!isset($this->function[$function])){

			$this->error = 'Function does not exist';
			$this->terminate = true;

			return false;

		}

		foreach($stack as &$param){

			$param = $this->return($param);

		}

		return $this->function[$function]($stack);

	}

	private function test($string){

		if(!$stack = $this->split(substr($string, 1, -1))){

			return false;

		}

		foreach($stack as &$param){

			$param = $this->return($param);

		}

		switch(count($stack)){

			case 3:

				if(!isset($this->operator[$stack[1]])){

					$this->error = "Operator ({$stack[1]}) does not exist";
					$this->terminate = true;

					return false;

				}

				if(!$this->validateCall($this->operator[$stack[1]], 2)){

					return false;

				}

				return $this->operator[$stack[1]]($this->getVar($stack[0]), $this->getVar($stack[2]));

				break;

			case 2:

				if(!isset($this->operator[$stack[0]])){

					$this->error = "Operator ({$stack[0]}) does not exist";
					$this->terminate = true;

					return false;

				}

				if(!$this->validateCall($this->operator[$stack[0]], 1)){

					return false;

				}

				return $this->operator[$stack[0]]($this->getVar($stack[1]));

				break;

			case 1:

				return !! $this->getVar($stack[0]);

				break;

		}

	}

	private function return($string, $base = false){

		if(is_array($string)){

			$this->error = 'Illegal parameter';
			$this->terminate = true;

			return false;

		}

		if(substr($string, 0, 1) === '[' && substr($string, -1) === ']'){

			return $this->execute($string);

		} 

		if($base){

			if($this->setVar()){

				return true;

			}

			$this->error = 'Unable to set variable';
			$this->terminate = true;

			return false;

		}

		if(substr($string, 0, 1) === '(' && substr($string, -1) === ')'){

			return $this->test($string);

		}else if(substr($string, 0, 1) === '"' && substr($string, -1) === '"'){

			return substr($string, 1, -1);

		}else if(isset($this->vars[$string])){

			return $this->vars[$string];

		}

		return $string;

	}

	public function error(){

		if(!$this->error){

			return false;

		}

		return "{$this->error} @ {$this->line}";

	}

	public function run($string){

		$this->parts = explode("\n", $string);

		$this->length = count($this->parts);

		$this->line = 0;

		while(!$this->terminate && $this->line < $this->length){

			if($this->part()){

				$this->return($this->part(), true);

				$this->steps++;

				if($this->steps > $this->maxSteps){

					$this->error = 'Step limit of ' . $this->maxSteps . ' preceded';
					$this->terminate = true;

				}

			}

			$this->line++;

		}

	}

}
