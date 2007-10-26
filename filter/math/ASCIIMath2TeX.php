<?php
/*
ASCIIMath2TeX.php
==============
This file contains functions to convert ASCII math notation
to TeX, which can be rendered via CGI to images.
Use:
	$AMT = new AMtoTeX;
	$tex = $AMT->convert($AMstring); //convert ASCIIMath string to TeX
	
Based on ASCIIMathML, Version 1.4.7 Aug 30, 2005, (c) Peter Jipsen http://www.chapman.edu/~jipsen

This is a PHP port of a Javascript modification of ASCIIMathML
Modified with TeX conversion for IMG rendering 
Sept 7, 2006 (c) David Lippman http://www.pierce.ctc.edu/dlippman

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or (at
your option) any later version.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License (at http://www.gnu.org/copyleft/gpl.html) 
for more details.
*/
class AMtoTeX 
{

var $decimalsign = "."; //change if needed - might mess up matrices if comma
var $AMpreviousSymbolinfix = false;
var $AMcurrentSymbolinfix = false;
var $AMnames = array();
var $AMnestingDepth = 0;

var $AMsymbols = array(

// Greek symbols
array( 'input'=>'alpha'),
array( 'input'=>'beta'),
array( 'input'=>'chi'),
array( 'input'=>'delta'),
array( 'input'=>'Delta'),
array( 'input'=>'epsi', 'tex'=>'epsilon'),
array( 'input'=>'varepsilon'),
array( 'input'=>'eta'),
array( 'input'=>'gamma'),
array( 'input'=>'Gamma'),
array( 'input'=>'iota'),
array( 'input'=>'kappa'),
array( 'input'=>'lambda'),
array( 'input'=>'Lambda'),
array( 'input'=>'mu'),
array( 'input'=>'nu'),
array( 'input'=>'omega'),
array( 'input'=>'Omega'),
array( 'input'=>'phi'),
array( 'input'=>'varphi'),
array( 'input'=>'Phi'),
array( 'input'=>'pi'),
array( 'input'=>'Pi'),
array( 'input'=>'psi'),
array( 'input'=>'rho'),
array( 'input'=>'sigma'),
array( 'input'=>'Sigma'),
array( 'input'=>'tau'),
array( 'input'=>'theta'),
array( 'input'=>'vartheta'),
array( 'input'=>'Theta'),
array( 'input'=>'upsilon'),
array( 'input'=>'xi'),
array( 'input'=>'alpha'),
array( 'input'=>'zeta'),

// Binary operation symbols
array( 'input'=>'*', 'tex'=>'cdot'),
array( 'input'=>'**', 'tex'=>'star'),
array( 'input'=>'//', 'output'=>'/'),
array( 'input'=>'\\\\', 'tex'=>'backslash'),
array( 'input'=>'setminus', 'output'=>'\\\\', 'definition'=>TRUE),
array( 'input'=>'xx', 'tex'=>'times'),
array( 'input'=>'-:', 'tex'=>'div'),
array( 'input'=>'divide', 'output'=>'-:', 'definition'=>TRUE),
array( 'input'=>'@', 'tex'=>'circ'),
array( 'input'=>'o+', 'tex'=>'oplus'),
array( 'input'=>'ox', 'tex'=>'otimes'),
array( 'input'=>'o.', 'tex'=>'odot'),
array( 'input'=>'sum', 'underover'=>TRUE),
array( 'input'=>'prod', 'underover'=>TRUE),
array( 'input'=>'^^', 'tex'=>'wedge'),
array( 'input'=>'^^^', 'tex'=>'bigwedge', 'underover'=>TRUE),
array( 'input'=>'vv', 'tex'=>'vee'),
array( 'input'=>'vvv', 'tex'=>'bigvee', 'underover'=>TRUE),
array( 'input'=>'nn', 'tex'=>'cap'),
array( 'input'=>'nnn', 'tex'=>'bigcap', 'underover'=>TRUE),
array( 'input'=>'uu', 'tex'=>'cup'),
array( 'input'=>'uuu', 'tex'=>'bigcup', 'underover'=>TRUE),

// Binary relation symbols
array( 'input'=>'!=', 'tex'=>'ne'),
array( 'input'=>':=' ), 			
array( 'input'=>'<', 'tex'=>'lt'),
array( 'input'=>'<=', 'tex'=>'le'),
array( 'input'=>'lt=', 'tex'=>'leq'),
array( 'input'=>'>', 'tex'=>'gt'), 
array( 'input'=>'>=', 'tex'=>'ge'),
array( 'input'=>'gt=', 'tex'=>'geq'),
array( 'input'=>'-<', 'tex'=>'prec'),
array( 'input'=>'-lt', 'output'=>'-<', 'definition'=>TRUE),
array( 'input'=>'>-', 'tex'=>'succ'),
array( 'input'=>'in'),
array( 'input'=>'!in', 'tex'=>'notin'),
array( 'input'=>'sub', 'tex'=>'subset'),
array( 'input'=>'sup', 'tex'=>'supset'),
array( 'input'=>'sube', 'tex'=>'subseteq'),
array( 'input'=>'supe', 'tex'=>'supseteq'),
array( 'input'=>'-=', 'tex'=>'equiv'),
array( 'input'=>'~=', 'tex'=>'stackrel{\sim}{=}', 'notexcopy'=>TRUE),
array( 'input'=>'cong', 'output'=>'~=', 'definition'=>TRUE),
array( 'input'=>'~~', 'tex'=>'approx'),
array( 'input'=>'prop', 'tex'=>'propto'),

// Logical symbols
array( 'input'=>'and', 'space'=>TRUE),
array( 'input'=>'or', 'space'=>TRUE),
array( 'input'=>'not', 'tex'=>'neg'),
array( 'input'=>'=>', 'tex'=>'Rightarrow'),
array( 'input'=>'implies', 'output'=>'=>', 'definition'=>TRUE),
array( 'input'=>'if', 'space'=>TRUE),
array( 'input'=>'<=>', 'tex'=>'Leftrightarrow'), 
array( 'input'=>'iff', 'output'=>'<=>', 'definition'=>TRUE),
array( 'input'=>'AA', 'tex'=>'forall'),
array( 'input'=>'EE', 'tex'=>'exists'),
array( 'input'=>'_|_', 'tex'=>'bot'),
array( 'input'=>'TT', 'tex'=>'top'),
array( 'input'=>'|--', 'tex'=>'vdash'),

// Miscellaneous symbols
array( 'input'=>'int'),
array( 'input'=>'dx', 'output'=>'{:d x:}', 'definition'=>TRUE),
array( 'input'=>'dy', 'output'=>'{:d y:}', 'definition'=>TRUE), 
array( 'input'=>'dz', 'output'=>'{:d z:}', 'definition'=>TRUE), 
array( 'input'=>'dt', 'output'=>'{:d t:}', 'definition'=>TRUE), 
array( 'input'=>'oint'),
array( 'input'=>'del', 'tex'=>'partial'),
array( 'input'=>'grad', 'tex'=>'nabla'),
array( 'input'=>'+-', 'tex'=>'pm'),
array( 'input'=>'O/', 'tex'=>'emptyset'),
array( 'input'=>'oo', 'tex'=>'infty'),
array( 'input'=>'aleph'),
array( 'input'=>'...', 'tex'=>'ldots'),
array( 'input'=>':.', 'tex'=>'therefore'),
array( 'input'=>'/_', 'tex'=>'angle'),
array( 'input'=>'\\ ', 'output'=>'\\ ', 'val'=>'true'),
array( 'input'=>'quad'),
array( 'input'=>'qquad'),
array( 'input'=>'cdots'),
array( 'input'=>'vdots'), 
array( 'input'=>'ddots'), 
array( 'input'=>'diamond'),
array( 'input'=>'square', 'tex'=>'boxempty'),
array( 'input'=>'|_', 'tex'=>'lfloor'),
array( 'input'=>'_|', 'tex'=>'rfloor'),
array( 'input'=>'|~', 'tex'=>'lceil'),
array( 'input'=>'lceiling', 'output'=>'|~', 'definition'=>TRUE),
array( 'input'=>'~|', 'tex'=>'rceil'),
array( 'input'=>'rceiling', 'output'=>'~|', 'definition'=>TRUE),
array( 'input'=>'CC', 'tex'=>'mathbb{C}', 'notexcopy'=>TRUE),
array( 'input'=>'NN', 'tex'=>'mathbb{N}', 'notexcopy'=>TRUE),
array( 'input'=>'QQ', 'tex'=>'mathbb{Q}', 'notexcopy'=>TRUE),
array( 'input'=>'RR', 'tex'=>'mathbb{R}', 'notexcopy'=>TRUE),
array( 'input'=>'ZZ', 'tex'=>'mathbb{Z}', 'notexcopy'=>TRUE),

// Standard functions
array( 'input'=>'lim', 'underover'=>TRUE),
array( 'input'=>'Lim', 'underover'=>TRUE), 
array( 'input'=>'sin', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'cos', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'tan', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'arcsin', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'arccos', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'arctan', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'sinh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'cosh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'tanh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'cot', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'sec', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'csc', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'coth', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'sech', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'csch', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'log', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'ln', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Sin', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Cos', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Tan', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Arcsin', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'Arccos', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'Arctan', 'unary'=>TRUE, 'func'=>TRUE), 
array( 'input'=>'Sinh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Cosh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Tanh', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Cot', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Sec', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Csc', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Coth', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Sech', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Csch', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Log', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Ln', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'abs', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'Abs', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'det', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'dim'),
array( 'input'=>'mod', 'tex'=>'text{mod}', 'notexcopy'=>TRUE),
array( 'input'=>'gcd', 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'lcm', 'tex'=>'text{lcm}', 'notexcopy'=>TRUE, 'unary'=>TRUE, 'func'=>TRUE),
array( 'input'=>'lub'), 
array( 'input'=>'glb'), 
array( 'input'=>'min', 'underover'=>TRUE), 
array( 'input'=>'max', 'underover'=>TRUE), 
array( 'input'=>'f', 'output'=>'f', 'unary'=>TRUE, 'func'=>TRUE,'val'=>TRUE),
array( 'input'=>'g', 'output'=>'g', 'unary'=>TRUE, 'func'=>TRUE,'val'=>TRUE),

// Arrows
array( 'input'=>'uarr', 'tex'=>'uparrow'),
array( 'input'=>'darr', 'tex'=>'downarrow'),
array( 'input'=>'rarr', 'tex'=>'rightarrow'),
array( 'input'=>'->', 'tex'=>'to'),
array( 'input'=>'|->', 'tex'=>'mapsto'), 
array( 'input'=>'larr', 'tex'=>'leftarrow'),
array( 'input'=>'harr', 'tex'=>'leftrightarrow'),
array( 'input'=>'rArr', 'tex'=>'Rightarrow'),
array( 'input'=>'lArr', 'tex'=>'Leftarrow'),
array( 'input'=>'hArr', 'tex'=>'Leftrightarrow'),

// Commands with argument
array( 'input'=>'sqrt', 'unary'=>TRUE ),
array( 'input'=>'Sqrt', 'unary'=>TRUE ),
array( 'input'=>'root', 'binary'=>TRUE ),
array( 'input'=>'frac', 'binary'=>TRUE),
array( 'input'=>'/', 'infix'=>TRUE),
array( 'input'=>'_', 'infix'=>TRUE),
array( 'input'=>'^', 'infix'=>TRUE),
array( 'input'=>'hat', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'bar', 'tex'=>'overline', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'vec', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'dot', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'ddot', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'ul', 'tex'=>'underline', 'unary'=>TRUE, 'acc'=>TRUE),
array( 'input'=>'text', 'text'=>TRUE),
array( 'input'=>'mbox', 'text'=>TRUE),
array( 'input'=>'"', 'text'=>TRUE),
array( 'input'=>'stackrel', 'binary'=>TRUE),

// Grouping brackets
array( 'input'=>'(', 'leftbracket'=>TRUE),
array( 'input'=>')', 'rightbracket'=>TRUE),
array( 'input'=>'[', 'leftbracket'=>TRUE),
array( 'input'=>']', 'rightbracket'=>TRUE),
array( 'input'=>'{', 'tex'=>'lbrace', 'leftbracket'=>TRUE),
array( 'input'=>'}', 'tex'=>'rbrace', 'rightbracket'=>TRUE),
array( 'input'=>'|', 'leftright'=>TRUE),
array( 'input'=>'(:', 'tex'=>'langle', 'leftbracket'=>TRUE),
array( 'input'=>':)', 'tex'=>'rangle', 'rightbracket'=>TRUE),
array( 'input'=>'{:', 'leftbracket'=>TRUE, 'invisible'=>TRUE),
array( 'input'=>':}', 'rightbracket'=>TRUE ,'invisible'=>TRUE),
array( 'input'=>'<<', 'tex'=>'langle', 'leftbracket'=>TRUE, 'notexcopy'=>TRUE), 
array( 'input'=>'>>', 'tex'=>'rangle', 'rightbracket'=>TRUE, 'notexcopy'=>TRUE), 

//fonts
array('input'=>'bb', 'tex'=>'mathbf', 'unary'=>TRUE),
array('input'=>'sf', 'tex'=>'mathsf', 'unary'=>TRUE),
array('input'=>'bbb', 'tex'=>'mathbb', 'unary'=>TRUE),
array('input'=>'cc', 'tex'=>'mathcal', 'unary'=>TRUE),
array('input'=>'tt', 'tex'=>'mathtt', 'unary'=>TRUE),
array('input'=>'fr', 'tex'=>'mathfrak', 'unary'=>TRUE)

);
function AMcompareNames($a,$b) {
	if ($a['input']>$b['input']) {return 1;} else {return -1;}
}

//original version
function AMinitSymbols() {
	
	$tsymb = array();
	for ($i=0; $i<count($this->AMsymbols); $i++) {
		if (isset($this->AMsymbols[$i]['tex']) && !isset($this->AMsymbols[$i]['notexcopy'])) {
			foreach($this->AMsymbols[$i] as $k=>$v) {
				if ($k!='tex') { $tsymb[$k]=$v;}
			}
			$tsymb['input']= $this->AMsymbols[$i]['tex'];
			array_push($this->AMsymbols, $tsymb);
		}
	}
	usort($this->AMsymbols, array($this,"AMcompareNames"));
	
	for ($i=0; $i<count($this->AMsymbols); $i++) {
		$this->AMnames[$i] = $this->AMsymbols[$i]['input'];
	}
}

function AMnewcommand($oldstr,$newstr) {
	
	array_push($this->AMsymbols, array('input'=>$oldstr, 'output'=>$newstr, 'definition'=>true));
}

function AMremoveCharsAndBlanks($str,$n) {
    	if (strlen($str)>1 && $str{$n}=='\\' && $str{$n+1}!= '\\' && $str{$n+1}!=' ') {
		$st = substr($str, $n+1);
	} else {
		$st = substr($str,$n);
	}
	$i = 0;
	while ($i<strlen($st) && ord($st{$i})<=32) {
		$i++;
	}
	return substr($st, $i);
}

function AMposition($arr, $str, $n) {
	if ($n==0) {
		$n = -1;
		$h = count($arr);
		while ($n+1<$h) {
			$m = ($n+$h) >> 1;
			if ($arr[$m]<$str) {
				$n = $m;
			} else {
				$h = $m;
			}
		}
		return $h;
	} else {
		$i = $n;
		while ($i<count($arr) && $arr[$i]<$str) {
			$i++;
		}
		return $i;
	}
}



function AMgetSymbol($str) {
	
	$k =0;
	$j =0;
	$match = "";
	$more = true;
	for ($i=1; $i<=strlen($str) && $more; $i++) {
		$st = substr($str,0,$i);
		$j = $k;
		$k = $this->AMposition($this->AMnames,$st,$j);
		if ($k<count($this->AMnames) && substr($str,0,strlen($this->AMnames[$k]))==$this->AMnames[$k]) {
			$match = $this->AMnames[$k];
			$mk = $k;
			$i = strlen($match);
		}
		$more = ($k<count($this->AMnames) && substr($str,0,strlen($this->AMnames[$k]))>=$this->AMnames[$k]);
	}
	$this->AMpreviousSymbolinfix = $this->AMcurrentSymbolinfix;
	if ($match!="") {
		$this->AMcurrentSymbolinfix = isset($this->AMsymbols[$mk]['infix']);
		return $this->AMsymbols[$mk];
	}
	$this->AMcurrentSymbolinfix = false;
	$k = 1;
	$st = substr($str,0,1);
	$integ = true;
	while ('0'<=$st && $st<='9' && $k<=strlen($str)) {
		$st = substr($str,$k,1);
		$k++;
	}
	if ($st == $this->decimalsign) {
		$st = substr($str,$k,1);
		if ('0'<=$st && $st<='9') {
			$integ = false;
			$k++;
			while ('0'<=$st && $st<='9' && $k<=strlen($str)) {
				$st = substr($str,$k,1);
				$k++;
			}
		}
	}
	if (($integ && $k>1) || $k>2) {
		$st = substr($str,0,$k-1);
		$isop = false;
	} else {
		$k = 2;
		$st = substr($str,0,1);
		$isop = (('A'>$st || $st>'Z') && ('a'>$st || $st>'z'));
	}
	if ($st=='-' && $this->AMpreviousSymbolinfix == true) {
		$this->AMcurrentSymbolinfix = true;
		return array('input'=>$st, 'unary'=>true, 'func'=>true, 'val'=>true);
	}
	return array('input'=>$st, 'val'=>true, 'isop'=>$isop);
}

function AMTremoveBrackets($node) {
	if ($node{0}=='{' && $node{strlen($node)-1}=='}') {
		$st = $node{1};
		if ($st=='(' || $st=='[') {
			$node = '{'.substr($node,2);
		}
		$st = substr($node,1,6);
		if ($st=='\\left(' || $st=='\\left[' || $st=='\\left{') {
			$node = '{'.substr($node,7);
		}
		$st = substr($node,1,12);
		if ($st=='\\left\\lbrace' || $st=='\\left\\langle') {
			$node = '{'.substr($node,13);
		}
		$st = $node{strlen($node)-2};
		if ($st==')' || $st==']') {
			$node = substr($node,0,strlen($node)-8).'}';
		}
		$st = substr($node,strlen($node)-8,7);
		if ($st=='\\rbrace' || $st=='\\rangle') {
			$node = substr($node,0,strlen($node)-14).'}';
		}
	}
	return $node;
}

/*Parsing ASCII math expressions with the following grammar
v ::= [A-Za-z] | greek letters | numbers | other constant symbols
u ::= sqrt | text | bb | other unary symbols for font commands
b ::= frac | root | stackrel         binary symbols
l ::= ( | [ | { | (: | {:            left brackets
r ::= ) | ] | } | :) | :}            right brackets
S ::= v | lEr | uS | bSS             Simple expression
I ::= S_S | S^S | S_S^S | S          Intermediate expression
E ::= IE | I/I                       Expression
Each terminal symbol is translated into a corresponding mathml node.*/

function AMTgetTeXsymbol($symb) {
	if (isset($symb['val'])) {
		$pre = '';
	} else {
		$pre = '\\';
	}
	if (isset($symb['tex'])) {
		return ($pre.$symb['tex']);
	} else {
		return ($pre.$symb['input']);
	}
}
function AMTgetTeXbracket($symb) {
	if (isset($symb['tex'])) {
		return ('\\'.$symb['tex']);
	} else {
		return $symb['input'];
	}
}

function AMTparseSexpr($str) { 
	
	$newFrag = '';
	$str = $this->AMremoveCharsAndBlanks($str,0);
	$symbol = $this->AMgetSymbol($str);
	
	if ($symbol == null || (isset($symbol['rightbracket']) && $this->AMnestingDepth>0)) {
		return array(null,$str);
	}
	if (isset($symbol['definition'])) {
		$str = $symbol['output'] . $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$symbol = $this->AMgetSymbol($str);
	}
	//underover and const handled in default
	if (isset($symbol['leftbracket'])) {
		$this->AMnestingDepth++;
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$result = $this->AMTparseExpr($str,true);
		$this->AMnestingDepth--;
		if (isset($symbol['invisible'])) {
			$node = '{\\left.' . $result[0] . '}';
		} else {
			$node = '{\\left' . $this->AMTgetTeXbracket($symbol) . $result[0] . '}';
		}
		return array($node, $result[1]);
	} else if (isset($symbol['text'])) {
		if ($symbol['input'] != '"') {
			$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		}
		if ($str{0}=='{') { $i = strpos($str,'}');}
		else if ($str{0}=='(') { $i = strpos($str,')');}
		else if ($str{0}=='[') { $i = strpos($str,']');}
		else if ($symbol['input']=='"') {
			$i = strpos(substr($str,1),'"')+1;
		} else { $i = 0;}
		if ($i==-1) { $i = strlen($str);}
		$st = substr($str,1,$i-1);
		if ($st{0}== " ") {
			$newFrag .= '\\ ';
		}
		$newFrag .= '\\text{'.$st.'}';
		if ($st{strlen($st)-1}== " ") {
			$newFrag .= '\\ ';
		}
		$str = $this->AMremoveCharsAndBlanks($str,$i+1);
		return array($newFrag, $str);
	} else if (isset($symbol['unary'])) {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$result = $this->AMTparseSexpr($str);
		if ($result[0]==null) {
			return array('{'.$this->AMTgetTeXsymbol($symbol).'}',$str);
		}
		if (isset($symbol['func'])) {
			$st = $str{0};
			if ($st=='^' || $st=='_' || $st=='/' || $st=='|' || $st==',') {
				return array('{'.$this->AMTgetTeXsymbol($symbol).'}',$str);
			} else {
				$node = '{'.$this->AMTgetTeXsymbol($symbol).'{'.$result[0].'}}';
				return array($node,$result[1]);
			}
		}
		$result[0] = $this->AMTremoveBrackets($result[0]);
		if ($symbol['input']=='sqrt') {
			return array('\\sqrt{'.$result[0].'}',$result[1]);
		} else if (isset($symbol['acc'])) {
			return array('{'.$this->AMTgetTeXsymbol($symbol).'{'.$result[0].'}}',$result[1]);
		} else {
			return array('{'.$this->AMTgetTeXsymbol($symbol).'{'.$result[0].'}}',$result[1]);
		}
	} else if (isset($symbol['binary'])) {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$result = $this->AMTparseSexpr($str);
		if ($result[0]==null) {
			return array('{'+$this->AMTgetTeXsymbol($symbol).'}', $str);
		}
		$result[0] = $this->AMTremoveBrackets($result[0]);
		$result2 = $this->AMTparseSexpr($result[1]);
		if ($result2[0]==null) {
			return array('{'+$this->AMTgetTeXsymbol($symbol).'}', $str);
		}
		$result2[0] = $this->AMTremoveBrackets($result2[0]);
		if ($symbol['input']=='root' || $symbol['input']=='stackrel') {
			if ($symbol['input']=='root') {
				$newFrag = '{\\sqrt['.$result[0].']{'.$result2[0].'}}';
			} else {
				$newFrag = '{'.$this->AMTgetTeXsymbol($symbol).'{'.$result[0].'}{'.$result2[0].'}}';
			}
		}
		if ($symbol['input']=='frac') {
			$newFrag = '{\\frac{'.$result[0].'}{'.$result2[0].'}}';
		}
		return array($newFrag,$result2[1]);
	} else if (isset($symbol['infix'])) {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		return array($symbol['input'], $str);
	} else if (isset($symbol['space'])) {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		return array('{\\quad\\text{'.$symbol['input'].'}\\quad}',$str);
	} else if (isset($symbol['leftright'])) {
		$this->AMnestingDepth++;
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$result = $this->AMTparseExpr($str,false);
		$this->AMnestingDepth--;
		$st = $result[0]{strlen($result[0])-1};
		if ($st == '|') {
			$node = '{\\left|'.$result[0].'}';
			return array($node,$result[1]);
		} else {
			$node = '{\\mid}';
			return array($node,$str);
		}
	} else {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$texsymbol = $this->AMTgetTeXsymbol($symbol);
		if ($texsymbol{0}=='\\' || (isset($symbol['isop']) && $symbol['isop']==true)) {
			return array($texsymbol,$str);
		} else {
			return array('{'.$texsymbol.'}',$str);
		}
	}
}

function AMTparseIexpr($str) {
	$str = $this->AMremoveCharsAndBlanks($str,0);
	$syml = $this->AMgetSymbol($str);
	$result = $this->AMTparseSexpr($str);
	$node = $result[0];
	$str = $result[1];
	$symbol = $this->AMgetSymbol($str);
	if (isset($symbol['infix'])==true && $symbol['input']!='/') {
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		$result = $this->AMTparseSexpr($str);
		if ($result[0]==null) {
			$result[0] = '{}';
		} else {
			$result[0] = $this->AMTremoveBrackets($result[0]);
		}
		$str = $result[1];
		if ($symbol['input']=='_') {
			$sym2 = $this->AMgetSymbol($str);
			$underover = isset($syml['underover']);
			if ($sym2['input']=='^') {
				$str = $this->AMremoveCharsAndBlanks($str,strlen($sym2['input']));
				$res2 = $this->AMTparseSexpr($str);
				$res2[0] = $this->AMTremoveBrackets($res2[0]);
				$str = $res2[1];
				$node = '{'.$node;
				$node .= '_{'.$result[0].'}';
				$node .= '^{'.$res2[0].'}';
				$node .= '}';
			} else {
				$node .= '_{'.$result[0].'}';
			}
		} else {
			$node = '{'.$node.'}^{'.$result[0].'}';
		}
	}
	
	return array($node,$str);
}

function AMTparseExpr($str,$rightbracket) {
	
	$newFrag = '';
	$addedright = false;
	do {
		$str = $this->AMremoveCharsAndBlanks($str,0);
		$result = $this->AMTparseIexpr($str);
		$node = $result[0];
		$str = $result[1];
		$symbol = $this->AMgetSymbol($str);
		if (isset($symbol['infix']) && $symbol['input']=='/') {
			$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
			$result = $this->AMTparseIexpr($str);
			if ($result[0]==null) {
				$result[0] = '{}';
			} else {
				$result[0] = $this->AMTremoveBrackets($result[0]);
			}
			$str = $result[1];
			$node = $this->AMTremoveBrackets($node);
			$node = '\\frac{'.$node.'}';
			$node .= '{'.$result[0].'}';
			$newFrag .= $node;
			$symbol = $this->AMgetSymbol($str);
		} else if ($node != '') {
			$newFrag .= $node;
		}
	} while ((!isset($symbol['rightbracket']) && (!isset($symbol['leftright']) || $rightbracket) || $this->AMnestingDepth==0) && $symbol!=null && $symbol['input']!='');
	if (isset($symbol['rightbracket']) || isset($symbol['leftright'])) {
		$len = strlen($newFrag);
		if ($len>2 && $newFrag{0}=='{' && strpos($newFrag,',')>0) {
			$right = $newFrag{$len-2};
			if ($right==')' || $right==']') {
				$left = $newFrag{6};
				if (($left=='(' && $right==')' && $symbol['input']!='}') || ($left=='[' && $right==']')) {
					$mxout = '\\matrix{';
					$pos = array();
					array_push($pos,0);
					$matrix = true;
					$mxnestingd = 0;
					$addedlast = true;
					for ($i=1; $i<$len-1;$i++) {
						if ($newFrag{$i}==$left) { $mxnestingd++;}
						if ($newFrag{$i}==$right) {
							$mxnestingd--;
							if ($mxnestingd==0 && $newFrag{$i+2}==',' && $newFrag{$i+3}=='{') {
									array_push($pos,$i+2);
							} else if ($mxnestingd==0 && $i+2<$len) {
								$matrix = false;
							}
						}
					}
					array_push($pos,$len);
					$lastmxsubcnt = -1;
					if ($mxnestingd==0 && count($pos)>0) {
						for ($i=0; $i<count($pos)-1;$i++) {
							if ($i>0) { $mxout .= '\\\\';}
							if ($i==0) {
								$subarr = explode(',',substr($newFrag,$pos[$i]+7,$pos[$i+1]-$pos[$i]-15));
							} else {
								$subarr = explode(',',substr($newFrag,$pos[$i]+8,$pos[$i+1]-$pos[$i]-16));
							}
							if ($lastmxsubcnt>0 && count($subarr)!=$lastmxsubcnt) {
								$matrix = false;
							} else if ($lastmxsubcnt==-1) {
								$lastmxsubcnt = count($subarr);
							}
							$mxout .= implode('&',$subarr);
						}
					}
					$mxout .= '}';
					if ($matrix) {
						$newFrag = $mxout;
					}
				}
			}
		}
		$str = $this->AMremoveCharsAndBlanks($str,strlen($symbol['input']));
		if (!isset($symbol['invisible'])) {
			$node = '\\right'.$this->AMTgetTeXbracket($symbol);
			$newFrag .= $node;
			$addedright = true;
		} else {
			$newFrag .= '\\right.';
			$addedright = true;
		}
	}
	if ($this->AMnestingDepth>0 && !$addedright) {
		$newFrag .= '\\right.';
	}
	return array($newFrag,$str);
}
function AMTparseAMtoTeX($str) {
	
	$this->AMnestingDepth = 0;
	$str = str_replace(array('&nbsp;','&gt;','&lt;'),array('','>','<'),$str);
	$str = preg_replace('/^\s+/','',$str);
	$result = $this->AMTparseExpr($str, false);
	return ($result[0]);
}

function AMtoTeX() { //constructor
	$this->AMinitSymbols();
}
function convert($str) {
	return $this->AMTparseAMtoTeX($str);
}

}
?>
