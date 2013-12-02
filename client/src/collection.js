/**
 * @class DL.Collection
 * @param {DL.Client} client
 * @param {String} name
 * @constructor
 */
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
DL.Collection.prototype.get = function(options) {
  var params = [],
      query = (this.hasWhere()) ? {q: this.wheres} : null;

  // clear wheres for future calls
  this.reset();

  if (typeof(options)!=="undefined") {
    if (options.paginate) {
      params.push('p=1');
    }
  }

  // + "?" + params.join('&')
  return this.client.get('collection/' + this.name , query);
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
  return this;
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
 * @method paginate
 * @return {DL.Pagination}
 */
DL.Collection.prototype.paginate = function(perPage, callback) {
  var that = this;

  if (!callback) {
    callback = perPage;
    perPage = 50;
  }

  this.get({paginate: true}).then(function(data) {
    callback(new DL.Pagination(that, data));
  });

  return this;
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

/**
 * @method each
 * @param {Function} callback
 */
DL.Collection.prototype.each = function(callback) { return this._iterate('each', callback); };

/**
 * @method find
 * @param {Function} callback
 */
DL.Collection.prototype.find = function(callback) { return this._iterate('find', callback); };

/**
 * @method filter
 * @param {Function} callback
 */
DL.Collection.prototype.filter = function(callback) { return this._iterate('filter', callback); };

/**
 * @method max
 * @param {Function} callback
 */
DL.Collection.prototype.max = function(callback) { return this._iterate('max', callback); };

/**
 * @method min
 * @param {Function} callback
 */
DL.Collection.prototype.min = function(callback) { return this._iterate('min', callback); };

/**
 * @method every
 * @param {Function} callback
 */
DL.Collection.prototype.every = function(callback, accumulator) { return this._iterate('every', callback); };

/**
 * @method reject
 * @param {Function} callback
 */
DL.Collection.prototype.reject = function(callback, accumulator) { return this._iterate('reject', callback, accumulator); };

/**
 * @method groupBy
 * @param {Function} callback
 */
DL.Collection.prototype.groupBy = function(callback, accumulator) { return this._iterate('groupBy', callback, accumulator); };

/**
 * Iterate using lodash function
 * @method _iterate
 * @param {String} method
 * @param {Function} callback
 * @param {Object} argument
 */
DL.Collection.prototype._iterate = function(method, callback, arg3) {
  var that = this;

  this.then(function(data) {
    _[method].call(that, data, callback, arg3);
  });

  return this;
};
