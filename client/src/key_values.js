/**
 * @class DL.KeyValues
 * @constructor
 * @param {DL.Client} client
 */
DL.KeyValues = function(client) {
  this.client = client;
};

DL.KeyValues.prototype.get = function(key) {
  return this.client.get('key/' + key);
};

DL.KeyValues.prototype.set = function(key, value) {
  return this.client.post('key/' + key, { value: value });
};
