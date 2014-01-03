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

function remp($str)
{
	if ($str == "") return $str;
	while (($str != "") && ($str[0] == '{') && (substr($str, -1, 1) == '}'))
	{
		$str = substr($str, 1, -1);
	}
	return $str;
}

function parse_nested($input) 
{
	$input = remp($input);

	$len = strlen($input);
	$substrings = array();
	$paren_count = 0;
	$operator = 0;
	$one_count = 0;
	$cur_string = '';
	for ($i = 0; $i < $len; $i++) 
	{
		$char = $input[$i];

		if (($operator) && (!ctype_alpha($char)))
		{
			$operator = 0;
			$substrings[] = $cur_string;
			$cur_string = '';			
		}

		if ($char == '}') 
		{
			$paren_count -= 1;
			if ($paren_count == 0)
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
		elseif ($char == '{') 
		{
			if (($paren_count == 0) && (strlen($cur_string)))
			{
				$substrings[] = $cur_string;
				$cur_string = '';
			}
			$one_count = 0;
			$paren_count += 1;
			$cur_string .= $char;
		}
		elseif ($char == ' ') 
		{
			if (($paren_count == 0) && (strlen($cur_string)))
			{
				$substrings[] = $cur_string;
				$cur_string = '';
			}
			$one_count = 0;
		}
		elseif (($char == '\\') && ($paren_count == 0))
		{
			if (strlen($cur_string))
			{
				$substrings[] = $cur_string;
				$cur_string = '';
			}
			$cur_string .= $char;
			$operator = 1;
		}
		elseif (($char == '_') && ($paren_count == 0))
		{
			$cur_string .= $char;
			$one_count = 1;
			if (strlen($cur_string))
			{
				$substrings[] = $cur_string;
				$cur_string = '';
			}
		}
		elseif (($char == '^') && ($paren_count == 0))
		{
			$cur_string .= $char;
			$one_count = 1;
			if (strlen($cur_string))
			{
				$substrings[] = $cur_string;
				$cur_string = '';
			}
		}
		else
		{
			$cur_string .= $char;
			if ($one_count)
			{
				$substrings[] = $cur_string;
				$cur_string = '';
				$one_count = 0;
			}
		}		
	}

	if (strlen($cur_string)) $substrings[] = $cur_string;	
	
	return $substrings;
}


function latex2mathml($str)
{
	$res = '';

	if (strlen($str) == 0) return $res;

	$parts = parse_nested($str);

	for ($i=0;$i<count($parts);$i++)
	{
		if (($parts[$i]=='\sqrt') && ($i+1 < count($parts)))
		{
			$res .= '<msqrt><mrow>'.latex2mathml($parts[$i+1]).'</mrow></msqrt>';
			$i += 1;
		}
		elseif (($parts[$i]=='\frac') && ($i+2 < count($parts)))
		{
			$res .= '<mfrac linethickness="1"><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+2]).'</mrow></mfrac>';
			$i += 2;
		}
		elseif ((substr($parts[$i],-1) == '_') && ($i+1 < count($parts)))
		{
			if (($i+3 < count($parts)) && ($parts[$i+2] == '^'))
			{
				$res .= '<msubsup><mrow>'.latex2mathml(substr($parts[$i],0,-1)).'</mrow><mrow>'.latex2mathml($parts[$i+1]).'</mrow><mrow>'.latex2mathml($parts[$i+3]).'</mrow></msubsup>';
				$i += 3;
			}
			else
			{
				$res .= '<msub><mrow>'.latex2mathml(substr($parts[$i],0,-1)).'</mrow><mrow>'.latex2mathml($parts[$i+1]).'</mrow></msub>';
				$i += 1;
			}
		}
		elseif ((substr($parts[$i],-1) == '^') && ($i+1 < count($parts)))
		{
			if (($i+3 < count($parts)) && ($parts[$i+2] == '_'))
			{
				$res .= '<msubsup><mrow>'.latex2mathml(substr($parts[$i],0,-1)).'</mrow><mrow>'.latex2mathml($parts[$i+3]).'</mrow><mrow>'.latex2mathml($parts[$i+1]).'</mrow></msubsup>';
				$i += 3;
			}
			else
			{
				$res .= '<msup><mrow>'.latex2mathml(substr($parts[$i],0,-1)).'</mrow><mrow>'.latex2mathml($parts[$i+1]).'</mrow></msup>';
				$i += 1;
			}
		}
		elseif ($parts[$i]=='\cdot')
		{
			$res .= '<mo>&sdot;</mo>';
		}
		elseif ($parts[$i]=='\tau')
		{
			$res .= '<mi>&tau;</mi>';
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

