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

// keywords for search engines
$KEYWORDS = array(
	'de' => array('WebLTX','Latex','PHP'),
	'en' => array('WebLTX','Latex','PHP')
);

// available languages
$LANGS_AVAILABLE = array('en','de');

// project name
$PROJECT_NAME = 'WebLTX';

// who is responsible for the content
$AUTHOR = 'Dr. Steffen Kühn';

// insert your copyright notion here
$COPYRIGHT = "WebLTX is free software and can be redistributed and modified under terms of the GNU General Public License.";

// set timezone
date_default_timezone_set('Europe/Berlin');

// error reporting ON/OFF
error_reporting(E_ALL | E_STRICT);

