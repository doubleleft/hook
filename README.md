dl-api
===

Installation
---

Database preferences are located on `app/config/database`. Currently supporting `mongodb`, `mysql` and `sqlite` drivers.

You must apply write permissions on app/storage directory: `chmod -R 777 app/storage`.

Enter 'api' directory and run `sudo composer install`

Commandline client
---

Enter 'commandline' directory on this repository and install it using `make`. It
will install `dl-api` client system-wide on `/bin` directory.

Client libraries
---

[JavaScript](https://github.com/doubleleft/dl-api-javascript) ([docs](http://doubleleft.github.io/dl-api-javascript))
