{% set proj_name = salt['pillar.get']('proj_name','myproject') %}
{% set www_root = salt['pillar.get']('project_path','/vagrant') %}
{% set user = salt['pillar.get']('project_username','vagrant') %}
{% set serv_name = grains['id'] %}
include:
  - mysql

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
    - unless: test -f {{ www_root }}/composer.phar
    - cwd: {{ www_root }}
    - require:
      - pkg: hook-deps

  file.managed:
    - name: {{ www_root }}/composer.phar
    - user: {{ user }}
    - mode: 0755
    - require: 
      - cmd: get-composer

check-composer:
  cmd.run:
    - name: php composer.phar self-update
    - user: {{ user }}
    - cwd: {{ www_root }}
    - require:
      - file: get-composer
    - onlyif: php composer.phar status | grep 'build of composer is over 30 days old' > /dev/null 2>&1 

install-hook:
  composer.installed:
    - name: {{ www_root }}
    - composer: {{ www_root }}/composer.phar
    - no_dev: true
    - prefer_dist: true
    - require: 
      - file: get-composer

{% if user != 'vagrant' %}
{{ www_root }}/public/storage:
  file.directory:
    - user: www-data
    - group: www-data
    - mode: 775
    - makedirs: True

{{ www_root }}/shared:
  file.directory:
    - user: {{ user }}
    - group: www-data
    - mode: 775
    - makedirs: True
{% endif %}

{{ www_root }}/config/database.php:
  file.managed:
    - source:
      - salt://hook/database.php
      - user: {{ user }}
      - mode: 644
      - backup: minion
    - template: jinja
    - require:
      - composer: install-hook

