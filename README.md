Welcome to hook ![Build status](https://travis-ci.org/doubleleft/hook.svg?branch=master)
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

To create and deploy hook apps, you'll need to clone and install
[hook-cli](https://github.com/doubleleft/hook-cli.git) (Commandline Interface)

Vagrant/Saltstack
---

Clone [doubleleft/hook](https://github.com/doubleleft/hook.git) repository, in your `/Projects` dir and cd into it.

Have a look in Vagrantfile and customize it for your needs.

Type: 

```bash
vagrant up
```

In order to deploy in a production server with [Saltstack](https://github.com/saltstack/salt), make sure you already have Salt installed. You can install it like this:

```bash
curl -L https://bootstrap.saltstack.com | sudo sh
```

Our salt formula accept some parameters. By default it should work out of the box in a Vagrant environment. The default values are setup like this:

```yaml
project_path: /vagrant
project_username: vagrant
proj_name: myproject
domain_name: localhost
```

If deploying through command line, you can customize this values like this:

```bash
cd your/directory/root/project
sudo salt-call -c salt state.highstate pillar='{project_path: your/directory/root/path, project_username: your-ssh-username, proj_name: hook, domain_name: hook.mydomain.com}'
```

If you are deploying inside vagrant itself through [vagrant-linode](https://github.com/displague/vagrant-linode), [vagrant-digitalocean](https://github.com/smdahlen/vagrant-digitalocean) or [vagrant-aws](https://github.com/mitchellh/vagrant-aws) for example, you could fill the salt pillar arguments right into `Vagrantfile`, like this, for ex:

```ruby
  config.vm.provision :salt do |salt|
    salt.minion_config = "salt/minion"
    salt.run_highstate = true
    salt.colorize = true
    salt.pillar({
      "project_path" => "/srv/www/hook",
      "project_username" => "ubuntu",
      "proj_name" => "hook",
      "domain_name" => "hook.mydomain.com"
    })
  end
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
