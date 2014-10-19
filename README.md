Welcome to hook ![Build status](https://magnum.travis-ci.com/doubleleft/hook.svg?token=px2fsDaL7WdoeJ9qixdB)
===

hook is a extendable Back-end as a Service (BaaS) that includes everything that you need
to create the back-end of your application.

Installation
---

Clone [doubleleft/hook](https://github.com/doubleleft/hook.git) repository, cd into it and run `make`:

```bash
git clone https://github.com/doubleleft/hook.git
cd hook
make
```

How to use
---

Take a look at the [wiki](https://github.com/doubleleft/hook/wiki) for more
details.

For client specific documentation:

- [JavaScript](https://github.com/doubleleft/hook-javascript) ([docs](http://doubleleft.github.io/hook-javascript))
- [Android](https://github.com/doubleleft/hook-android) (_docs missing_)
- [iOS](https://github.com/doubleleft/hook-ios) (_docs missing_)
- [C++](https://github.com/doubleleft/hook-cpp) (_docs missing_)
- [Ruby](https://github.com/doubleleft/hook-ruby) (_docs missing_)
- [PHP](https://github.com/doubleleft/hook-php) (_docs missing_)
- [C#](https://github.com/doubleleft/hook-csharp) (_docs missing_)

Websocket
---

For the websocket itself:

```bash
php socket/server.php
```

And you may also need to setup a socket policy server:

```bash
perl -Tw socket/flash_socketpolicy.pl
```

Its set to listen on port 8430 in order to be able to run it as an unprivileged user, but as the script needs to bind in port 843 we can forward ports.

With iptables we can apply the following rule (of curse with `sudo` or as `root` user):

```bash
sudo iptables -t nat -A PREROUTING -p tcp --dport 843 -j REDIRECT --to-port 8430
```

Or with ipfw on Mac OS X:
```bash
sudo ipfw add 100 fwd 127.0.0.1,8430 tcp from any to me 843 in
```

License
---

MIT. Please see LICENSE file.
