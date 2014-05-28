dl-api socket server
===

Run the server:

    $ php socket/server.php

If you're using [web-socket-js](https://github.com/gimite/web-socket-js) to
support IE9<, you'll also need to listen to 843 port, to serve Flash Socket
Policy File.

    $ ./flash_socketpolicy.pl
