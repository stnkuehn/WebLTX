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

include_once('numbering.php');
include_once('interface.php');

// current site index
$Page = 0;
// current languages
$Language = $LANGS_AVAILABLE[0];
// available html files
$AllHTMFiles = array();
// version
$WebLTX_Version = '1.0';

function main()
{
	global $Page;
	global $Language;
	global $AllHTMFiles;
	global $LANGS_AVAILABLE;

	// Als Trennzeichen ; statt & benutzen
	ini_set('arg_separator.output','&amp;');
	ini_set('url_rewriter.tags', '');

	// which language should be used
	$Language = GetLanguageCode();

	// Verzeichnis mit den Inhalten
	$ContentDir = './content/'.$Language.'/';

	// Verzeichnis mit dem Template
	$TemplateDir = './template/';

	// Finde alle Verzeichnisse und Dateien im Unterverzeichnis
	$AllHTMFiles = FindAllHTMFiles($ContentDir);

	// Welche Datei dieser Liste soll nun angezeigt werden?
	$Page = GetPage($AllHTMFiles);

	// Erzeuge symbolische Links
	CreateLinks($AllHTMFiles);

	// Eintrag ins Logfile
	MakeLogEntry();

	// Template laden
	include($TemplateDir."template.htm"); 
}

function GetLanguageCode()
{
	global $LANGS_AVAILABLE;
	
	$lang_brw = substr(filter_input(INPUT_SERVER,'HTTP_ACCEPT_LANGUAGE'), 0, 2);
	$lang_get = filter_input(INPUT_GET,'lang');
	if ($lang_get == NULL)
	{
		$lang = $lang_brw;
	}
	else
	{
		$lang = $lang_get;
	}
	if (in_array($lang,$LANGS_AVAILABLE))
	{
		return $lang;
	}
	else
	{
		return $LANGS_AVAILABLE[0];
	}
}

function CollectGlobalData($AllHTMFiles)
{
	global $Page;

	$sec_save = $Page;

	for ($Page=0;$Page<sizeof($AllHTMFiles);$Page++)
	{
		// execute the embedded php code but show nothing
		ob_start();
		include($AllHTMFiles[$Page]);
		ob_end_clean();
	}

	$Page = $sec_save;
}

function MakeLogEntry()
{
	// create directory when it isn't existing
	$LogDir = getcwd().'/log';
	$LogFile = $LogDir.'/logfile.txt';

	if (!file_exists($LogDir))
	{
		mkdir($LogDir);
		chmod($LogDir, 0770);
	}

	file_put_contents($LogFile,
		date("Y-m-d, H:i:s",time()) .
		"\t" . filter_input(INPUT_SERVER,'REMOTE_ADDR') .
		"\t" . filter_input(INPUT_SERVER,'REQUEST_METHOD') .
		"\t" . filter_input(INPUT_SERVER,'REQUEST_URI') .
		"\t" . filter_input(INPUT_SERVER,'HTTP_USER_AGENT') .
		"\t" . filter_input(INPUT_SERVER,'HTTP_REFERER') .
		"\t" . gethostbyaddr(filter_input(INPUT_SERVER,'REMOTE_ADDR')) .
		"\t" . filter_input(INPUT_SERVER,'REMOTE_PORT') .
		"\r\n",
		FILE_APPEND
	);
	chmod($LogFile, 0660);
}

// Erzeugt Keywordeintrag
function GetKeywordsGeneric()
{
	global $KEYWORDS;
	global $Language;

	$str = '<meta name="keywords" lang="'.$Language.'" content="';
	$len = count($KEYWORDS[$Language]);
	for ($i=0; $i<$len; $i++)
	{
		$str .= $KEYWORDS[$Language][$i];
		if ($i < $len-1) $str .= ',';
	}
	$str .= '">';
	return $str;
}

// Liefert den Index der darzustellenden Seite
function GetPage($AllHTMFiles)
{
	$page = 0;

	// Wurde der Page-Index als URL-Parameter übergeben?
	$paget = filter_input(INPUT_GET,'page');
	if ((isset($paget)) && (isset($AllHTMFiles[$paget]))) $page = $paget;

	return $page;
}

function ListFiles($Dir)
{
	$fnd = array();
	$handle=opendir($Dir);
	while ($file = readdir($handle)) 
	{
		if (($file != ".") & ($file != "..") & ($file[0] != "."))
		{
			array_push($fnd,$Dir.$file);
		}
	}
	closedir($handle);
	sort($fnd);
	return $fnd;
}

// Findet alle *.htm Dateien in einem Verzeichnis und dessen Unterverzeichnissen
function FindAllHTMFiles($Dir)
{
	$fnd = array();

	// Enthält das Verzeichnis irgendwo ein Unterverzeichnis .svn, dann nicht suchen
	$parts = explode("/",$Dir);
	foreach($parts as $part)
	{
		if ($part == ".svn") return $fnd;
	}

	$files = ListFiles($Dir);
	foreach ($files as $file)
	{
		$parts = explode("/",$file);
		$last = $parts[count($parts)-1];

		if ($last[0] != "#") 
		{
			if (is_dir($file) & ($file != ".") & ($file != ".."))
			{
				$res = FindAllHTMFiles($file."/");
				foreach ($res as $part)
				{
					array_push($fnd,$part);
				}
			}
			else
			{
				if (substr_count($file,".htm") != 0)
				{
					array_push($fnd,$file);
				}
			}
		}
	}

	return $fnd;
}

// Wann wurden die Dateien zuletzt geändert
function LastUpdateGeneric()
{
	global $AllHTMFiles;

	$maxtime = 0;
	foreach ($AllHTMFiles as $file)
	{
		$time = filemtime($file);
		if ($time > $maxtime)
		{
			$maxtime = $time;
		}
	}
	return $maxtime;
}


