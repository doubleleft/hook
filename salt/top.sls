base:
  '* and not gateway.doubleleft.com':
    - match: compound
    - mysql
    - nginx.template
    - php_fpm

  '*':
    - base
    - nginx.template
    - php_fpm
    - hook
    
