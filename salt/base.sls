{% set user = salt['pillar.get']('project_username','vagrant') %}

git:
  pkg.installed

git_https:
  cmd.run: 
    - name: git config --global url."https://".insteadOf git://
    - user: {{ user }}
    - require: 
      - pkg: git

