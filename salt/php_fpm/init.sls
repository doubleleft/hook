www-data:
  user.present:
    - shell: /sbin/nologin
    - home: False
    - system: False

# PHP5 modules and configuration
{% if grains['os_family'] == 'RedHat' %}
php-epel:
  cmd.run:
    - name: curl -L http://www.atomicorp.com/installers/atomic | grep -v "check_input \"" | sudo sh
    - unless: test -e /etc/yum.repos.d/atomic.repo

  {% set v = "" %}
  {% set root = "/etc" %}
  {% set fpm = "/etc/php-fpm.d" %}
  {% set apc = "/etc/php.d" %}
  {% set pecl = "-pecl" %}
  {% set pid = "/var/run/php-fpm/php-fpm.pid" %}
{% elif grains['os_family'] == 'Debian' %}
  {% set v = "5" %}
  {% set root = "/etc/php5/fpm" %}
  {% set fpm = "/etc/php5/fpm/pool.d" %}
  {% set apc = "/etc/php5/fpm/conf.d" %}
  {% set pecl = "" %}
  {% set pid = "/var/run/php5-fpm.pid" %}
{% endif %}

php_stack:
  pkg.installed:
    - name: php{{ v }}-fpm
    {% if grains['os_family'] == 'RedHat' %}
    - repo: remi
    - require:
      - cmd: php-epel
      - user: www-data
    {% endif %}
  service.running:
    - name: php{{ v }}-fpm
    - enable: True
    - require:
      - pkg: php{{ v }}-fpm
      - pkg: php{{ v }}-gd
      - pkg: php{{ v }}-mysql
      - pkg: php{{ v }}{{ pecl }}-memcache
      - pkg: php{{ v }}-mcrypt
      - pkg: php{{ v }}-cli
      - pkg: php{{ pecl }}-apc
      - pkg: php{{ v }}-json
      {% if grains['os_family'] == 'Debian' %}
      - pkg: php5-curl
      {% elif grains['os_family'] == 'RedHat' %}
      - pkg: php-common
      - pkg: php-pear-Net-Curl
      {% endif %}
    - watch:
      - file: {{ root }}/php-fpm.conf
      - file: {{ fpm }}/www.conf
      - file: {{ apc }}/apc.ini
      - file: {{ root }}/php.ini

php_fpm:
  pkg.installed:
    - name: php{{ v }}-fpm
    {% if grains['os_family'] == 'RedHat' %}
    - require:
      - pkg: php-common
    {% endif %}

{% if grains['os_family'] == 'RedHat' %}
php_common:
  pkg.installed:
    - name: php{{ v }}-common
{% endif %}

php_gd:
  pkg.installed:
    - name: php{{ v }}-gd

php_mysql:
  pkg.installed:
    - name: php{{ v }}-mysql

php_memcache:
  pkg.installed:
    - name: php{{ v }}{{ pecl }}-memcache

php_mcrypt:
  pkg.installed:
    - name: php{{ v }}-mcrypt

php_curl:
  pkg.installed:
    {% if grains['os_family'] == 'RedHat' %}
    - name: php-pear-Net-Curl
    {% elif grains['os_family'] == 'Debian' %}
    - name: php5-curl
    {% endif %}

php_cli:
  pkg.installed:
    - name: php{{ v }}-cli

php_apc:
  pkg.installed:
    {% if grains['os_family'] == 'Debian' %}
    - name: php-apc
    {% elif grains['os_family'] == 'RedHat' %}
    - name: php-pecl-apc
    {% endif %}

php_json:
  pkg.installed:
    - name: php{{ v }}-json

# Configuration files for php5-fpm
{{ root }}/php-fpm.conf:
  file.managed:
    - source: salt://php_fpm/php-fpm.conf
    - template: jinja
    - user: root
    - group: root
    - mode: 644
    - defaults:
        fpm: {{ fpm }}
        pid: {{ pid }}
    - require:
        - pkg: php{{ v }}-fpm 

{{ fpm }}/www.conf:
  file.managed:
    - source: salt://php_fpm/www.conf
    - template: jinja
    - user: root
    - group: root
    - mode: 644
    - require:
      - pkg: php{{ v }}-fpm 

{{ apc }}/apc.ini:
  file.managed:
    - source: salt://php_fpm/apc.ini
    - template: jinja
    - user: root
    - group: root
    - mode: 644
    - require:
      - pkg: php{{ pecl }}-apc

{{ root }}/php.ini:
  file.managed:
    - source: salt://php_fpm/php.ini
    - template: jinja
    - user: root
    - group: root
    - mode: 644
    - require:
      - pkg: php{{ v }}-fpm
