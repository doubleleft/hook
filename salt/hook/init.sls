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
    - unless: test -f /usr/local/bin/composer
    - cwd: /root/
    - require:
      - pkg: hook-deps

install-composer:
  cmd.wait:
    - name: mv /root/composer.phar /usr/local/bin/composer
    - cwd: /root
    - watch:
      - cmd: get-composer

install-hook:
  cmd.watch:
    - name: make
    - user: {{ user }}
    - cwd: {{ www_root }}
    - watch:
      - cmd: test ! -d {{ www_root }}/vendor

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

