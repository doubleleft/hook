/**
 * @class DL.Client
 * @constructor
 * @param {Object} options
 *   @param {String} appId
 *   @param {String} key
 *   @param {String} url DL base url
 */
DL.Client = function(options) {
  this.url = options.url || "http://dl-api.dev/";
  this.appId = options.appId;
  this.key = options.key;

  /**
   * @property {KeyValues} keys
   */
  this.keys = new DL.KeyValues(this);
};

/**
 * Get collection instance
 * @param {String} collectionName
 * @return {DL.Collection}
 */
DL.Client.prototype.collection = function(collectionName) {
  return new DL.Collection(this, collectionName);
};

/**
 * Get collection instance
 * @param {String} collectionName
 * @return {DL.Collection}
 */
DL.Client.prototype.collection = function(collectionName) {
  return new DL.Collection(this, collectionName);
};

DL.Client.prototype.post = function(segments, data) {
  if (typeof(data)==="undefined") {
    data = {};
  }
  return this.request(segments, "POST", data);
};

/**
 * @method get
 * @param {String} segments
 * @param {Object} data
 */
DL.Client.prototype.get = function(segments, data) {
  return this.request(segments, "GET", data);
};

/**
 * @method put
 * @param {String} segments
 * @param {Object} data
 */
DL.Client.prototype.put = function(segments, data) {
  return this.request(segments, "PUT", data);
};

/**
 * @method delete
 * @param {String} segments
 */
DL.Client.prototype.delete = function(segments) {
  return this.request(segments, "DELETE");
};

/**
 * @method request
 * @param {String} segments
 * @param {String} method
 * @param {Object} data
 */
DL.Client.prototype.request = function(segments, method, data) {
  var payload, deferred = when.defer();

  if (data) {
    payload = JSON.stringify(data);

    if (method === "GET") {
      payload = encodeURIComponent(payload);
    }
  }

  uxhr(this.url + segments, payload, {
    method: method,
    headers: {
      'X-App-Id': this.appId,
      'X-App-Key': this.key,
      'Content-Type': 'application/json' // exchange data via JSON to keep basic data types
    },
    success: function(response) {
      deferred.resolver.resolve(JSON.parse(response));
    },
    error: function(response) {
      deferred.resolver.reject(JSON.parse(response));
    }
  });

  return deferred.promise;
};

DL.Client.prototype.serialize = function(obj, prefix) {
  var str = [];
  for (var p in obj) {
    if (obj.hasOwnProperty(p)) {
      var k = prefix ? prefix + "[" + p + "]" : p,
      v = obj[p];
      str.push(typeof v == "object" ? this.serialize(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
    }
  }
  return str.join("&");
};
