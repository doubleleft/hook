DL.Collection = function(client, name) {
  this.client = client;

  this.name = name;
  this.wheres = [];
};

/**
 * Create a new resource
 * @method create
 * @param {Object} data
 */
DL.Collection.prototype.create = function(data) {
  return this.client.post('collection/' + this.name, { data: data });
};

/**
 * Get collection data, based on `where` params.
 * @method get
 */
DL.Collection.prototype.get = function() {
  return this.client.get('collection/' + this.name);
};

/**
 * Add `where` param
 * @param {Object} where params or field name
 */
DL.Collection.prototype.where = function(objects, _operation, _value) {
  var field,
      operation = (typeof(_value)==="undefined") ? '=' : _operation,
      value = (typeof(_value)==="undefined") ? _operation : _value;

  if (typeof(objects)==="object") {
    for (field in objects) {
      if (objects.hasOwnProperty(field)) {
        this.addWhere(field, '=', objects[field]);
      }
    }
  } else {
    this.addWhere(objects, operation, value);
  }

  return this;
};

DL.Collection.prototype.addWhere = function(field, operation, value) {
  this.wheres.push([field, operation, value]);
};

/**
 * Update a single collection entry
 * @param {String} id
 * @param {Object} data
 */
DL.Collection.prototype.update = function(id, data) {
  throw new Exception("Not implemented.");
};

/**
 * Update all collection's data based on `where` params.
 * @param {Object} data
 */
DL.Collection.prototype.updateAll = function(data) {
  throw new Exception("Not implemented.");
};
