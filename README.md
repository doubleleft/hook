Welcome to hook
===

[![Build status](https://travis-ci.org/doubleleft/hook.svg?branch=master)](https://travis-ci.org/doubleleft/hook)
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/doubleleft/hook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

hook is a extendable multitenant Back-end as a Service (BaaS) that provides
data persistance, authentication, real-time communication and much more to
create the back-end of your web or mobile application.

It follows the same principles from [nobackend](http://nobackend.org/), [hoodie.js](https://github.com/hoodiehq/hoodie.js) and [Parse](http://parse.com)

**Requirements**: PHP 5.4+, or [PHP 5.3](https://github.com/doubleleft/hook/wiki/Deploying-on-PHP-5.3).

- [Features](#features)
- [Installation](#installation)
- [How to use](#how-to-use)
- [Documentation](https://github.com/doubleleft/hook/wiki)
- [Front-end integration](#front-end-integration)

Features
---

- Multitenancy (same instance may be used for many apps)
- User authentication (register, login, reset password)
- Data persistance through `collections`
- Data storage through [many providers](https://github.com/doubleleft/hook/wiki/Storage-providers)
- Real-time communication through [WAMP](http://wamp.ws) subprotocol (WebSockets).
- [Package management](https://github.com/doubleleft/hook/wiki/Composer-dependencies) through composer

Installation
---

Run this command in your terminal to get the lastest
version:

```bash
curl -sSL https://raw.githubusercontent.com/doubleleft/hook/master/scripts/install.sh | bash
```

At the end of the process you should have
[hook](https://github.com/doubleleft/hook) and
[hook-cli](https://github.com/doubleleft/hook-cli.git) installed in your
machine.

How to use
---

Use the following command to create a new application from the commandine.

```
hook app:new my-app --endpoint http://localhost/hook/public/index.php/
```

It will output access keys to use in the front-end. Checkout this example using
[JavaScript](https://github.com/doubleleft/hook-javascript#how-to-use) frontend.

Take a look at the [documentation](https://github.com/doubleleft/hook/wiki) for
more details.

Front-end Integration
---

Reduce the gap between backend and frontend development:

- [JavaScript](https://github.com/doubleleft/hook-javascript) ([docs](http://doubleleft.github.io/hook-javascript))
- [C# / Unity3D](https://github.com/doubleleft/hook-csharp) (_docs missing_)
- [Corona SDK](https://github.com/doubleleft/hook-corona-sdk) (_docs missing_)
- [PHP](https://github.com/doubleleft/hook-php) ([docs](http://doubleleft.github.io/hook-php))
- [Ruby](https://github.com/doubleleft/hook-ruby) ([docs](http://doubleleft.github.io/hook-ruby/))
- Java/Android (soon)
- ObjectiveC/iOS (soon)
- C++ (soon)

License
---

MIT.
