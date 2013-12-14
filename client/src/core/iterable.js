/**
 * @class DL.Iterable
 */
DL.Iterable = function() { };
DL.Iterable.prototype = {
  /**
   * @method each
   * @param {Function} callback
   */
  each : function(callback) { return this._iterate('each', callback); },

  /**
   * @method find
   * @param {Function} callback
   */
  find : function(callback) { return this._iterate('find', callback); },

  /**
   * @method filter
   * @param {Function} callback
   */
  filter : function(callback) { return this._iterate('filter', callback); },

  /**
   * @method max
   * @param {Function} callback
   */
  max : function(callback) { return this._iterate('max', callback); },

  /**
   * @method min
   * @param {Function} callback
   */
  min : function(callback) { return this._iterate('min', callback); },

  /**
   * @method every
   * @param {Function} callback
   */
  every : function(callback, accumulator) { return this._iterate('every', callback); },

  /**
   * @method reject
   * @param {Function} callback
   */
  reject : function(callback, accumulator) { return this._iterate('reject', callback, accumulator); },

  /**
   * @method groupBy
   * @param {Function} callback
   */
  groupBy : function(callback, accumulator) { return this._iterate('groupBy', callback, accumulator); },

  /**
   * Iterate using lodash function
   * @method _iterate
   * @param {String} method
   * @param {Function} callback
   * @param {Object} argument
   */
  _iterate : function(method, callback, arg3) {
    var that = this;

    this.then(function(data) {
      _[method].call(that, data, callback, arg3);
    });

    return this;
  }
};
