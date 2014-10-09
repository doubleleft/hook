{% set proj_name = salt['pillar.get']('proj_name','myproject') -%}
{% set mysql_user = proj_name|replace('-','')|truncate(15) -%}
{% set mysql_pass = salt['grains.get']('mysql:' ~ mysql_user ~ '') -%}
{% set mysql_db = mysql_user -%}
<?php

return array(
        'mysql' => array(
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'username' => '{{ mysql_user }}',
		'password' => '{{ mysql_pass }}',
		'database' => '{{ mysql_db }}',
		'collation' => 'utf8_general_ci',
		'charset' => 'utf8'
	),
);
