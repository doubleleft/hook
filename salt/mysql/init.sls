{% set user = salt['pillar.get']('project_username','deploy') %}
{% set proj_name = salt['pillar.get']('proj_name','myproject') %}
{% set root = salt['pillar.get']('project_path','/vagrant') %}
{% set mysql_user = proj_name|replace('-','')|truncate(15) %}
{% set mysql_db = mysql_user %}
{% set mysql_root_password = salt['pillar.get']('mysql:server:root_password', salt['grains.get']('server_id')) %}

mysql-server:
  pkg.installed: []
  service.running:
    - name: mysql
    - enable: True
    - require:
      - pkg: mysql-server

{% if not ( 'ddll' in grains['host'] or 'staging' in grains['host'] ) %}
mysql_debconf:
  debconf.set:
    - name: mysql-server
    - data:
        'mysql-server/root_password': {'type': 'password', 'value': '{{ mysql_root_password }}'}
        'mysql-server/root_password_again': {'type': 'password', 'value': '{{ mysql_root_password }}'}
        'mysql-server/start_on_boot': {'type': 'boolean', 'value': 'true'}
{% endif %}

mysql:
  service.running:
    - name: mysql
    - require:
      - pkg: mysql-server

python-mysqldb:
  pkg.installed

dbconfig:
  mysql_user.present:
    - name: {{ mysql_user }}
    - password: "{{ salt['grains.get_or_set_hash']('mysql:' ~ mysql_user ~ '') }}"
    - require:
      - service: mysql
      - pkg: python-mysqldb

  mysql_database.present:
    - name: {{ mysql_db }}
    - require:
      - mysql_user: dbconfig

  mysql_grants.present:
    - grant: all privileges
    - database: {{ mysql_db }}.*
    - user: {{ mysql_user }}
    - require:
      - mysql_database: dbconfig 


