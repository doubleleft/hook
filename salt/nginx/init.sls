{% if grains['os_family'] == 'Debian' %}
  {% set sites_enabled = "/etc/nginx/sites-enabled" %}
  {% set ext = "" %}
{% elif grains['os_family'] == 'RedHat' %}
  {% set sites_enabled = "/etc/nginx/conf.d" %}
  {% set ext = ".conf" %}
{% endif%}

nginx:
  pkg:
    - installed
  service.running:
    - enable: True
    - watch:
        - file: time-log
        - file: default-nginx

nginx-service:
  service.running:
    - name: nginx
    - enable: True
    - watch:
        - file: time-log
        - file: default-nginx
    - require:
        - pkg: nginx
        - file: default-nginx

time-log:
  file.managed:
    - name: /etc/nginx/conf.d/time_log.conf
    - source: salt://nginx/time_log.conf
    - require:
        - pkg: nginx

default-nginx:
  file.absent:
    - name: {{ sites_enabled }}/default{{ ext }}
    - require:
        - pkg: nginx
       
