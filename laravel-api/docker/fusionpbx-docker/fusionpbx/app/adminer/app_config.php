<?php

	//application details
		$apps[$x]['name'] = "Adminer";
		$apps[$x]['uuid'] = "214b9f02-547b-d49d-f4e9-02987d9581c5";
		$apps[$x]['category'] = "System";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "3.2.2";
		$apps[$x]['license'] = "http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0";
		$apps[$x]['url'] = "http://www.adminer.org/";
		$apps[$x]['description']['en-us'] = "Adminer (formerly phpMinAdmin) is a full-featured database management tool written in PHP. Adminer is available for MySQL, PostgreSQL, SQLite, MS SQL and Oracle.";
		$apps[$x]['description']['ar-eg'] = "";
		$apps[$x]['description']['de-at'] = "Adminer (ehemals phpMinAdmin) ist ein umfassendes Werkzeug für die Datenbankverwaltung welches in PHP geschrieben wurde. Es ist für MySQL, PostgreSQL, SQLite, MS SQL und Oracle verfügbar.";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-de'] = "Adminer (ehemals phpMinAdmin) ist ein umfassendes Werkzeug für die Datenbankverwaltung welches in PHP geschrieben wurde. Es ist für MySQL, PostgreSQL, SQLite, MS SQL und Oracle verfügbar.";
		$apps[$x]['description']['es-cl'] = "Adminer (anteriormente phpMinAdmin) es una herramienta completa para la gestión de bases de datos escrita en PHP. Adminer está disponible para MySQL, PostgreSQL, SQLite, MS SQL y Oracle)";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-fr'] = "Adminer (précédemment phpMinAdmin) est un outil gestion de base de données complet écrite en php. Adminer est disponible pour MySQL, PostgreSQL, SQLite, MS SQL et Oracle.";
		$apps[$x]['description']['he-il'] = "";
		$apps[$x]['description']['it-it'] = "";
		$apps[$x]['description']['nl-nl'] = "";
		$apps[$x]['description']['pl-pl'] = "";
		$apps[$x]['description']['pt-br'] = "";
		$apps[$x]['description']['pt-pt'] = "Adminer (anteriormente phpMinAdmin) é uma ferramenta completa para gestão de bases de dados escrita em PHP. O Adminer está disponível para MySQL, PostgreSQL, SQLite, MS SQL e Oracle.";
		$apps[$x]['description']['ro-ro'] = "";
		$apps[$x]['description']['ru-ru'] = "Adminer (в прошлом phpMinAdmin) это полнофункциональный инструмент управления базами данных, написанный на PHP. Adminer доступен для for MySQL, PostgreSQL, SQLite, MS SQL и Oracle.";
		$apps[$x]['description']['sv-se'] = "";
		$apps[$x]['description']['uk-ua'] = "";

	//permission details
		$apps[$x]['permissions'][0]['name'] = "adminer";
		$apps[$x]['permissions'][0]['menu']['uuid'] = "1f59d07b-b4f7-4f9e-bde9-312cf491d66e";
		$apps[$x]['permissions'][0]['groups'][] = "superadmin";

	//default settings
		$y=0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "e34b276a-1b64-4d11-9470-ae3ea977c47e";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "adminer";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "auto_login";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "boolean";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "Set whether to auto-login to Adminer, or require a username and password.";

?>