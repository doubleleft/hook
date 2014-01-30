/**
 * @class DL.KeyValues
 * @constructor
 * @param {Client} client
 */
DL.Stream = function(collection, options) {
  if (!options) { options = {}; }

  this.collection = collection;

  // time to wait for retry, after connection closes
  this.retry_timeout = options.retry_timeout || 5;
  this.refresh_timeout = options.refresh_timeout || 5;
  this.from_now = options.from_now || false;

  var query = this.collection.buildQuery();
  query['X-App-Id'] = this.collection.client.appId;
  query['X-App-Key'] = this.collection.client.key;
  query.from_now = this.from_now;
  query.stream = {
    'retry': this.retry_timeout,
    'refreh': this.refresh_timeout
  };

  this.event_source = new EventSource(this.collection.client.url + this.collection.segments + "?" + JSON.stringify(query), {
    withCredentials: true
  });

  // bind event source
  if (typeof(options)==="function") {
    this.on('message', options);
  } else {
    for (var event in options) {
      this.on(event, options[event]);
    }
  }
};

/**
 * Register event handler
 * @method on
 * @param {String} event
 * @param {Function} callback
 * @return {Stream} this
 */
DL.Stream.prototype.on = function(event, callback) {
  var that = this;

  if (event == 'message') {
    this.event_source.onmessage = function(e) {
      callback.apply(that, [JSON.parse(e.data), e]);
    };
  } else {
    this.event_source['on' + event] = function(e) {
      callback.apply(that, [e]);
    };
  }

  return this;
};

/**
 * Close streaming connection
 * @method close
 * @return {Stream} this
 */
DL.Stream.prototype.close = function() {
  this.event_source.close();
  return this;
};
