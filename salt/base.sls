{% set user = salt['pillar.get']('project_username','vagrant') %}

git:
  pkg.installed

git_https:
  cmd.run: 
    - name: git config --global url.ssh://git@github.com/.insteadOf https://github.com/
    - user: {{ user }}
    - require: 
      - pkg: git

/home/{{ user }}/.ssh/id_rsa:
  file.managed:
    - source: salt://base/devops
    - user: {{ user }}
    - mode: 0600
    - makedirs: True


