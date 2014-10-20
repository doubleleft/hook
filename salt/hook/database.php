{% set proj_name = salt['pillar.get']('proj_name','myproject') -%}
{% set mysql_user = proj_name|replace('-','')|truncate(15) -%}
{% set mysql_pass = salt['grains.get']('mysql:' ~ mysql_user ~ '') -%}
{% set mysql_db = mysql_user -%}
{% if grains['host'] in ['odesmistificador'] -%}
  {% set mysql_host = 'shell-mitos.crectshle2hx.us-east-1.rds.amazonaws.com' -%}
{% else -%}
  {% set mysql_host = 'localhost' -%}
{% endif -%}
<?php

return array(
		'driver'   => 'mysql',
		'host'     => '{{ mysql_host }}',
		'username' => '{{ mysql_user }}',
		'password' => '{{ mysql_pass }}',
		'database' => '{{ mysql_db }}',
		'collation' => 'utf8_general_ci',
		'charset' => 'utf8'
);
