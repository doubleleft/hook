{% set proj_name = salt['pillar.get']('proj_name','myproject') %}
{% set www_root = salt['pillar.get']('project_path','/vagrant') %}
{% set user = salt['pillar.get']('project_username','vagrant') %}
{% set serv_name = grains['id'] %}

git:
  pkg.installed

git.config_set:
  module.run:
    - setting_name: url.https://.insteadOf
    - setting_value: git://
    - user: {{ user }}
    - is_global: True
    - cwd: {{ www_root }}
    - require:
      - pkg: git

