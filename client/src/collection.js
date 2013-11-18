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
  var query = (this.hasWhere()) ? {q: this.wheres} : null;
  return this.client.get('collection/' + this.name, query);
};

/**
 * Add `where` param
 * @param {Object | String} where params or field name
 * @param {String} operation operation or value
 * @param {String} value value
 */
DL.Collection.prototype.where = function(objects, _operation, _value) {
  var field,
      operation = (typeof(_value)==="undefined") ? '=' : _operation,
      value = (typeof(_value)==="undefined") ? _operation : _value;

  if (typeof(objects)==="object") {
    for (field in objects) {
      if (objects.hasOwnProperty(field)) {
        if (objects[field] instanceof Array) {
          operation = objects[field][0];
          value = objects[field][1];
        } else {
          value = objects[field];
        }
        this.addWhere(field, operation, value);
      }
    }
  } else {
    this.addWhere(objects, operation, value);
  }

  return this;
};

/**
 * alias for get & then
 * @method then
 */
DL.Collection.prototype.then = function() {
  var promise = this.get();
  promise.then.apply(promise, arguments);
  return promise;
};

DL.Collection.prototype.addWhere = function(field, operation, value) {
  this.wheres.push([field, operation, value]);
};

/**
 * Clear collection where statements
 * @method reset
 */
DL.Collection.prototype.reset = function() {
  this.wheres = [];
  return this;
};

/**
 * @method hasWhere
 * @return {Boolean}
 */
DL.Collection.prototype.hasWhere = function() {
  return this.wheres.length > 0;
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
