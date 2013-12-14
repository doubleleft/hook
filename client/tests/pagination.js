asyncTest("Pagination", function() {
  expect(7);

  var posts = client.collection('posts');
  posts.orderBy('created_at', -1);

  posts.paginate(function(pagination) {
    ok(pagination.isFetching() == false);
    ok(pagination.per_page === 50);
    ok(pagination.current_page === 1);
    ok(pagination.last_page >= 1);
    ok(pagination.from == 1);
    ok(pagination.to >= 1);
    ok(pagination.data.length > 0);

    start();
  });
});

