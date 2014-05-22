Welcome to dl-api
===

dl-api is a backend-as-a-service platform that includes everything that you need
to create web applications, without writing back-end code. It is flexible
enought if you need a custom behaviour.

Installation
---

Firstly, You need [node.js](http://nodejs.org/) installed for commandline
support.

Now, clone [doubleleft/dl-api](https://github.com/doubleleft/dl-api.git) repository, cd into it and run `sudo make`:

```bash
git clone https://github.com/doubleleft/dl-api.git
cd dl-api
sudo make
```

Configuration
---

Optionally configure your database preferences at `app/config/database`. By
default it uses `sqlite` as driver. The following additional drivers are
supported: `mysql`, `postgres`, `sqlserver` and `mongodb`.

NOTE: It's recommended to give ownership to your server's user on storage
directory: e.g. (`chown -R www-data app/storage`).

How to use
---

Take a look at the [wiki](https://github.com/doubleleft/dl-api/wiki) for more
details.

For client specific documentation:

- [JavaScript](https://github.com/doubleleft/dl-api-javascript) ([docs](http://doubleleft.github.io/dl-api-javascript))
- [Android](https://github.com/doubleleft/dl-api-android) (_docs missing_)
- [iOS](https://github.com/doubleleft/dl-api-ios) (_docs missing_)
- [C++](https://github.com/doubleleft/dl-api-cpp) (_docs missing_)
