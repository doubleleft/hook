/**
 * @class DL.KeyValues
 * @constructor
 * @param {DL.Client} client
 */
DL.KeyValues = function(client) {
  this.client = client;
};

/**
 * @method get
 * @param {String} key
 * @param {Function} callback
 * @return {Promise}
 */
DL.KeyValues.prototype.get = function(key, callback) {
  var promise = this.client.get('key/' + key);
  if (callback) {
    promise.then.apply(promise, [callback]);
  }
  return promise;
};

DL.KeyValues.prototype.set = function(key, value) {
  return this.client.post('key/' + key, { value: value });
};
