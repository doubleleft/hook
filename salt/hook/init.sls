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
    - unless: test -f {{ www_proj }}/composer.phar
    - cwd: {{ www_proj }}
    - require:
      - pkg: hook-deps

  file.managed:
    - name: {{ www_proj }}/composer.phar
    - user: {{ user }}
    - mode: 0755
    - require: 
      - cmd: get-composer

install-hook:
  composer.installed:
    - composer: {{ www_proj }}/composer.phar
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
      - cmd: install-hook

