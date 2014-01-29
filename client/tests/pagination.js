asyncTest("Pagination", function() {
  expect(7);

  var posts = client.collection('posts');
  posts.sort('created_at', -1);

  posts.paginate(function(pagination) {
    ok(pagination.isFetching() == false, "is fetching");
    ok(pagination.per_page === 50, "per_page");
    ok(pagination.current_page === 1, "current_page");
    ok(pagination.last_page >= 1, "last_page");
    ok(pagination.from == 1, "from");
    ok(pagination.to >= 1, "to");
    ok(pagination.data.length > 0, "data");

    start();
  });
});

