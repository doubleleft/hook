{% import "base.sls" as base with context %}
{% import "mysql.sls" as mysql with context %}

hook-deps:
  pkg.installed:
    - pkgs:
      - git
      - npm
      - nodejs
      - nodejs-legacy
      - php5-cli

get-composer:
  cmd.run:
    - name: 'CURL=`which curl`; $CURL -sS https://getcomposer.org/installer | php'
    - unless: test -f {{ base.www_root }}/composer.phar
    - cwd: {{ base.www_root }}
    - require:
      - pkg: hook-deps

composer-install:
  cmd.run:
    - name: php composer.phar self-update
    - user: {{ base.user }}
    - cwd: {{ base.www_root }}
    - require:
      - cmd: get-composer
      - pkg: hook-deps
    - onlyif: php composer.phar status | grep 'build of composer is over 30 days old' > /dev/null 2>&1 

  composer.installed:
    - name: {{ base.www_root }}
    - composer: {{ base.www_root }}/composer.phar
    - no_dev: true
    - prefer_dist: true
    - require: 
      - cmd: composer-install

{% if base.user != 'vagrant' %}
{{ base.www_root }}/public/storage:
  file.directory:
    - user: www-data
    - group: www-data
    - mode: 775
    - makedirs: True

{{ base.www_root }}/shared:
  file.directory:
    - user: {{ user }}
    - group: www-data
    - mode: 775
    - makedirs: True
{% endif %}

{{ base.www_root }}/config/database.php:
  file.managed:
    - source: salt://hook/database.php
    - user: {{ base.user }}
    - context: 
      mysql_host: {{ mysql.mysql_host }}
      mysql_user: {{ mysql.mysql_user }}
      mysql_pass: {{ mysql.mysql_pass }}
      mysql_db: {{ mysql.mysql_db }}
    - template: jinja
    - require:
      - composer: composer-install

