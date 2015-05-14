<?php 

/*
    WebLTX - Very lightweight web framework for publishing scientific 
    stuff as fast and easily as with Latex 
	
    Copyright (C) 2014  Dr. Steffen Kühn / steffen.kuehn@em-sys-dev.de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$paren_open = array('{','(','[');
$paren_close = array('}',')',']');

$symbols = array(
	'\,' => '&#8290;',
	'\;' => '&nbsp;',
	'\int' => '&int;',
	'\infty' => '&#x221E;',
	'\alpha' => '&alpha;',
	'\Beta' => '&Beta;',	
	'\beta' => '&beta;',
	'\Gamma' => '&Gamma;',
	'\gamma' => '&gamma;',
	'\Delta' => '&Delta;',
	'\delta' => '&delta;',
	'\Epsilon' => '&Epsilon;',
	'\epsilon' => '&epsilon;',
	'\Zeta' => '&Zeta;',
	'\zeta' => '&zeta;',
	'\Eta' => '&Eta;',
	'\eta' => '&eta;',
	'\Theta' => '&Theta;',
	'\theta' => '&theta;',
	'\Iota' => '&Iota;',
	'\iota' => '&iota;',
	'\Kappa' => '&Kappa;',
	'\kappa' => '&kappa;',
	'\Lambda' => '&Lambda;',
	'\lambda' => '&lambda;',
	'\Mu' => '&Mu;',
	'\mu' => '&mu;',
	'\Nu' => '&Nu;',
	'\nu' => '&nu;',
	'\Xi' => '&Xi;',
	'\xi' => '&xi;',
	'\Omicron' => '&Omicron;',
	'\omicron' => '&omicron;',
	'\Pi' => '&Pi;',
	'\pi' => '&pi;',
	'\Rho' => '&Rho;',
	'\rho' => '&rho;',
	'\Sigma' => '&Sigma;',
	'\sigma' => '&sigma;',
	'\Tau' => '&Tau;',
	'\tau' => '&tau;',
	'\Upsilon' => '&Upsilon;',
	'\upsilon' => '&upsilon;',
	'\Phi' => '&Phi;',
	'\phi' => '&phi;',
	'\Chi' => '&Chi;',
	'\chi' => '&chi;',
	'\Psi' => '&Psi;',
	'\psi' => '&psi;',
	'\Omega' => '&Omega;',
	'\omega' => '&omega;',
	'\in' => '&#x2208',
	'\approx' => '&#x2248;'
);

function is_zero($a)
{
	for ($i=0;$i<sizeof($a);$i++)
	{
		if ($a[$i] != 0) return false;
	}
	return true;
}

// normalize whitespaces
function normalize($input)
{
	return preg_replace('/\s+/', ' ', $input);
}

// for processing of blocks with parenthesis
function parse_blocks_inner($input) 
{
	global $paren_open;
	global $paren_close;
	
	$paren_count = array();
	for ($i=0;$i<sizeof($paren_open);$i++) $paren_count[] = 0;
	$substrings = array();	
	$cur_string = '';
	for ($i = 0; $i < strlen($input); $i++) 
	{
		$char = $input[$i];
		
		if (in_array($char,$paren_close))
		{
			$pos = array_search($char,$paren_close);
			$paren_count[$pos] -= 1;
			if (is_zero($paren_count))
			{
				$cur_string .= $char;
				$substrings[] = $cur_string;
				$cur_string = '';
			}
			else
			{
				$cur_string .= $char;
			}
		}
		elseif (in_array($char,$paren_open))
		{
			$pos = array_search($char,$paren_open);
			if (is_zero($paren_count))
			{
				if (strlen($cur_string) != 0) $substrings[] = $cur_string;
				$cur_string = '';
			}
			$paren_count[$pos] += 1;
			$cur_string .= $char;
		}
		else
		{
			$cur_string .= $char;
		}		
	}

	// append the rest
	if (strlen($cur_string)) $substrings[] = $cur_string;	
	
	return $substrings;
}

function parse_blocks($input) 
{
	global $paren_open;
	
	if (in_array($input[0],$paren_open))
	{
		$ret = parse_blocks_inner(substr($input, 1, -1));
		array_unshift($ret,$input[0]);
		$ret[] = $input[strlen($input)-1];
		return $ret;
	}
	else
	{
		return parse_blocks_inner($input); 
	}
}

// subscripts are nasty to handle. we change the order here to make the processing easier
function process_subscripts($in)
{
	// a^b => ^ab, a_b => _ab, a^b_c => ^_abc, a_b^c => ^_acb, \int\limits_a^b => 
	$limits = array();
	$lst = array();
	for ($i=0;$i<sizeof($in);$i++)
	{
		if ($in[$i] != '\limits')
		{
			$lst[] = $in[$i];
		}
		else
		{
			$limits[] = sizeof($lst);
		}
	}
	for ($i=0;$i<3;$i++)
	{
		$lst[] = '@dummy';
	}
	
	$out = array();
	
	for ($i=0;$i<sizeof($lst)-3;$i++)
	{
		if (($lst[$i+1]=='_') && ($lst[$i+3]=='^'))
		{
			$sym = '^_';
			if (in_array($i+1,$limits)) $sym .= 'L';
			$out[] = $sym;
			$out[] = $lst[$i];
			$out[] = $lst[$i+4];
			$out[] = $lst[$i+2];
			$i += 4;
		}
		elseif (($lst[$i+1]=='^') && ($lst[$i+3]=='_'))
		{
			$sym = '^_';
			if (in_array($i+1,$limits)) $sym .= 'L';
			$out[] = $sym;
			$out[] = $lst[$i];
			$out[] = $lst[$i+2];
			$out[] = $lst[$i+4];
			$i += 4;
		}
		elseif (($lst[$i+1]=='_') && ($lst[$i+3]!='^'))
		{
			$sym = '_';
			if (in_array($i+1,$limits)) $sym .= 'L';
			$out[] = $sym;
			$out[] = $lst[$i];
			$out[] = $lst[$i+2];
			$i += 2;
		}
		elseif (($lst[$i+1]=='^') && ($lst[$i+3]!='_'))
		{
			$sym = '^';
			if (in_array($i+1,$limits)) $sym .= 'L';
			$out[] = $sym;
			$out[] = $lst[$i];
			$out[] = $lst[$i+2];
			$i += 2;
		}
		else
		{
			if ($lst[$i] != '@dummy') $out[] = $lst[$i];
		}
	}
	
	return $out;
}

function parse_nested($input) 
{
	global $paren_open;
	$substrings = array();
	$parts = parse_blocks($input);
	
	for ($i=0;$i<sizeof($parts);$i++)
	{
		if (in_array($parts[$i][0],$paren_open))
		{
			$substrings[] = $parts[$i];
		}
		else
		{
			$substr = '';
			$str = $parts[$i];
			for ($j = 0; $j < strlen($str); $j++) 
			{
				if (in_array($str[$j],array('\\','=')))
				{		
					if ($substr != '') $substrings[] = $substr;
					$substr = $str[$j];
				}
				elseif (in_array($str[$j],array('^','_')))
				{
					if ($substr != '') $substrings[] = $substr;
					$substrings[] = $str[$j];
					$substr = '';
				}
				elseif (in_array($str[$j],array(';',',','.')))
				{
					$substr .= $str[$j];
					$substrings[] = $substr;
					$substr = '';
				}
				elseif (in_array($str[$j],array(' ','=')))
				{
					if ($substr != '') $substrings[] = $substr;
					$substr = '';
				}
				else
				{
					$substr .= $str[$j];
				}
			}
			if ($substr != '') $substrings[] = $substr;
		}		
	}
	
	$substrings_filter = process_subscripts($substrings);
	
	return $substrings_filter;
}

function latex2mathml($str)
{
	global $paren_open;
	global $paren_close;
	global $symbols;
	
	$res = '';	
	if (strlen($str) == 0) return $res;	
	$str = normalize($str);
	$parts = parse_nested($str);

	for ($i=0;$i<count($parts);$i++)
	{
		if (($parts[$i] == '{') || ($parts[$i] == '}'))
		{
			$i += 0;
		}
		elseif (in_array($parts[$i],$paren_open) || in_array($parts[$i],$paren_close))
		{
			$res .= $parts[$i];
		}
		elseif ((in_array($parts[$i][0],$paren_open)) && (strlen($parts[$i]) > 1))
		{
			$res .= latex2mathml($parts[$i]);
		}
		elseif ($parts[$i]=='\sqrt')
		{
			$res .= '<msqrt><mrow>'.latex2mathml($parts[$i+1]).'</mrow></msqrt>';
			$i += 1;
		}
		elseif ($parts[$i]=='\frac')
		{
			$res .= '<mfrac linethickness="1"><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+2]).'</mrow></mfrac>';
			$i += 2;
		}
		elseif ($parts[$i]=='\mathrm')
		{
			$res .= '<mo>'.substr($parts[$i+1], 1, -1).latex2mathml($parts[$i+2]).'</mo>';
			$i += 2;
		}
		elseif ($parts[$i]=='^')
		{
			$res .= '<msup><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+2]).'</mrow></msup>';
			$i += 2;
		}
		elseif ($parts[$i]=='_')
		{
			$res .= '<msub><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+2]).'</mrow></msub>';
			$i += 2;
		}
		elseif ($parts[$i]=='^_')
		{
			$res .= '<msubsup><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+3]).'</mrow><mrow>'.latex2mathml($parts[$i+2]).'</mrow></msubsup>';
			$i += 3;
		}
		elseif ($parts[$i]=='^L')
		{
			$res .= '<munderover><mo>'.latex2mathml($parts[$i+1]).'</mo><mo></mo><mo>'.latex2mathml($parts[$i+2]).'</mo></munderover>';
			$i += 2;
		}
		elseif ($parts[$i]=='_L')
		{
			$res .= '<munderover><mo>'.latex2mathml($parts[$i+1]).'</mo><mo>'.latex2mathml($parts[$i+2]).'</mo><mo></mo></munderover>';
			$i += 2;
		}
		elseif ($parts[$i]=='^_L')
		{
			$res .= '<munderover><mo>'.latex2mathml($parts[$i+1]).'</mo><mo>'.latex2mathml($parts[$i+3]).'</mo><mo>'.latex2mathml($parts[$i+2]).'</mo></munderover>';
			$i += 3;
		}
		elseif (array_key_exists($parts[$i],$symbols))
		{
			$res .= '<mo>'.$symbols[$parts[$i]].'</mo>';
		}
		else
		{
			$res .= preg_replace(
				array(
					'/([a-z|A-Z]+)/',
					'/([\+|\-|=|\(|\)|\.|\'])/',
					'/([0-9]+)/',					
				),array(
					'<mi>$1</mi>',
					'<mo>$1</mo>',
					'<mn>$1</mn>',					
				),$parts[$i]);
		}
	}

	return $res;
}

