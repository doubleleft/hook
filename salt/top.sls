base:
  '* and not gateway.doubleleft.com':
    - match: compound
    - mysql
    - sites.template
    - php_fpm

  '*':
    - base
    - sites.template
    - php_fpm
    - nginx
    - hook
    
