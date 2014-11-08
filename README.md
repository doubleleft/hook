Welcome to hook
===

![Build status](https://travis-ci.org/doubleleft/hook.svg?branch=master)
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/doubleleft/hook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

hook is a extendable Back-end as a Service (BaaS) that includes everything that
you need to create the back-end of your application.

- [Installation](#installation)
- [How to use](#how-to-use)
- [Documentation](https://github.com/doubleleft/hook/wiki)
- [Client implementations](#client-implementations)


Requirements
---

- PHP 5.4+

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

Client implementations
---

- [JavaScript](https://github.com/doubleleft/hook-javascript) ([docs](http://doubleleft.github.io/hook-javascript))
- [Android](https://github.com/doubleleft/hook-android) (_docs missing_)
- [iOS](https://github.com/doubleleft/hook-ios) (_docs missing_)
- [C++](https://github.com/doubleleft/hook-cpp) (_docs missing_)
- [Ruby](https://github.com/doubleleft/hook-ruby) (_docs missing_)
- [PHP](https://github.com/doubleleft/hook-php) ([docs](http://doubleleft.github.io/hook-php))
- [C#](https://github.com/doubleleft/hook-csharp) (_docs missing_)

License
---

MIT. Please see LICENSE file.
