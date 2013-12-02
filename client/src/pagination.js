/**
 * @class DL.Pagination
 * @param {DL.Collection} collection
 * @param {Number} perPage
 * @constructor
 */
DL.Pagination = function(collection, response) {
  this.collection = collection;

  this.total = response.total;
  this.per_page = response.per_page;
  this.current_page = response.current_page;
  this.last_page = response.last_page;
  this.from = response.from;
  this.to = response.to;
  this.data = response.data;
};

DL.Pagination.prototype.hasNext = function() {
  return (this.current_page < this.to);
};

DL.Pagination.prototype.then = function() {
};
