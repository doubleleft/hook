/**
 * @class KeyValues
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
  for (var event in options) {
    this.on(event, options[event]);
  }
};

DL.Stream.prototype.on = function(event, callback) {
  if (event == 'message') {
    this.event_source.onmessage = function(e) {
      callback(JSON.parse(e.data), e);
    };
  } else {
    this.event_source['on' + event] = callback;
  }
};
