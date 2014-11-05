{% import "base.sls" as base with context %}

include:
  - nginx

{% if grains['os_family'] == 'Debian' %}
  {% set sites_enabled = "/etc/nginx/sites-enabled" %}
{% elif grains['os_family'] == 'RedHat' %}
  {% set sites_enabled = "/etc/nginx/conf.d" %}
{% endif%}

nginx-conf:
  file.directory:
    - names:
      - {{ base.www_root }}
    - user: {{ base.user }}
    - group: {{ base.user }}
    - makedirs: True
    - unless: test -d {{ base.www_root }}

{% if grains['host'] in ['staging','ddll'] %}
nginx-conf-available:
  file.managed:
    - name: /etc/nginx/sites-available/01-{{ base.proj_name }}.ddll.co.conf
    - source: salt://sites/template.conf
    - template: jinja
    - watch_in:
      - service: nginx
    - defaults:
        ssl: False
{% else %}
nginx-conf-available:
  file.managed:
    - name: /etc/nginx/sites-available/{{ base.proj_name }}.conf
    - source: salt://sites/template.conf
    - template: jinja
    - watch_in:
      - service: nginx
    - defaults:
        ssl: False
{% endif %}

nginx-conf-enabled:
  file.symlink:
    - name: {{ sites_enabled }}/{{ base.proj_name }}.conf
    - target: /etc/nginx/sites-available/{{ base.proj_name }}.conf
    - watch_in:
      - service: nginx
