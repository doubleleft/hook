(function () {
  'use strict';

  var fs = require('fs'),
      util = require('util'),
      repl = require('repl'),
      jsdom = require('jsdom'),
      html = '<html><body></body></html>',
      Table = require('cli-table'),
      XMLHttpRequest = require('xmlhttprequest'),
      FormData = require('form-data'),
      Blob;

  console.log("     _ _                   _                             _       ");
  console.log("  __| | |       __ _ _ __ (_)   ___ ___  _ __  ___  ___ | | ___  ");
  console.log(" / _` | |_____ / _` | '_ \\| |  / __/ _ \\| '_ \\/ __|/ _ \\| |/ _ \\ ");
  console.log("| (_| | |_____| (_| | |_) | | | (_| (_) | | | \\__ \\ (_) | |  __/ ");
  console.log(" \\__,_|_|      \\__,_| .__/|_|  \\___\\___/|_| |_|___/\\___/|_|\\___| ");
  console.log("                    |_|                                          ");
  console.log("");
  process.stdout.write("Loading...");

  // first argument can be html string, filename, or url
  jsdom.env(html, ["https://dl-api.ddll.co/dist/dl.js"], function (errors, window) {
    console.log("\rAPI Documentation: http://doubleleft.github.io/dl-api-javascript\n");
    console.log("Available variables to hack on:");
    console.log("\t- dl - DL.Client");
    console.log("\t- config - .dl-config");
    console.log("\t- $ - jQuery 2.1.0");
    console.log("\t- window");

    // Define browser features
    // -----------------------
    // dummy localstorage
    window.localStorage = {
      _items: {},
      getItem: function(name) { return this._items[name]; },
      setItem: function(name, value) { this._items[name] = value; }
    };
    window.FormData = FormData;
    window.Blob = function Blob() {};
    window.Blob.constructor = Buffer.prototype;

    function writer(obj) {
      var that = this;

      if(obj.constructor.name == 'Promise'){
        obj.then(function(data) {
          prettyPrint(data,that);
        }).catch(function(data) {
          prettyPrint(data,that);
        });
        return "[ Running... ]";
      } else {
        return util.inspect(obj, {colors: true});
      }
    }

    function prettyPrint(data, pointer){
      var options = {};

      // Print table for arrays
      if (data.length && data.length > 0) {
        delete data[0].app_id;

        if (!options.timestamps) {
          delete data[0].created_at;
          delete data[0].updated_at;
        }

        var keys = Object.keys(data[0]),
            table = new Table({ head: keys });

        for (var i=0; i < data.length; i++) {
          delete data[i].app_id;

          if (!options.timestamps) {
            delete data[i].created_at;
            delete data[i].updated_at;
          }

          var values = [];
          for (var k in data[i]) {
            values.push(util.inspect(data[i][k], {colors: true}));
          }
          table.push(values);
        }

        pointer.outputStream.write("\n" + table.toString() + "\n");
      } else if (data.lengh == 0) {
        pointer.outputStream.write("\nEmpty.\n");
      } else {
        // Pretty general output
        pointer.outputStream.write("\n" + util.inspect(data, {colors: true}) + "\n");
      }
      pointer.displayPrompt();
    }

    var $ = require('jquery')(window),
        sess = repl.start({
          prompt: 'dl-api: javascript> ',
          writer: writer,
          ignoreUndefined: true
        }),
        config = JSON.parse(fs.readFileSync(process.argv[2]));

    //
    // TODO: always use the same coding style for this
    //
    config.appId = config.app_id;
    delete config.app_id;

    config.url = config.endpoint;
    delete config.endpoint;

    var _request = window.DL.Client.prototype.request;
    window.DL.Client.prototype.request = function(segments, method, data) {
      if (typeof(data)==="undefined") { data = {}; }
      data._sync = true;
      return _request.apply(this, arguments);
    }

    //
    // Custom inspecting
    //
    // window.DL.Collection.prototype.inspect = function() {
    //   return "[Collection: '" + this.name + "']";
    // };

    sess.context.window = window;
    sess.context.$ = window.$;
    sess.context.DL = window.DL;
    sess.context.config = config;
    sess.context.dl = new window.DL.Client(config);
    sess.context.sess = sess;

  });
}());
