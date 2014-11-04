{% set user = salt['pillar.get']('project_username','vagrant') %}
{% set www_root = salt['pillar.get']('project_path','/vagrant') %}

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

