asyncTest("Collections", function() {
  expect(2);

  var posts = client.collection('posts');

  posts.create({
    title: "My awesome blog post",
    content: "Lorem ipsum dolor sit amet."
  }).then(function(response) {
    ok(response.title ==  "My awesome blog post", "CREATE");
  }, function(response) {
    ok(false, "CREATE");
  });

  posts.get().then(function(response) {
    ok(response.length > 0 && response[response.length-1].title == "My awesome blog post", "LIST");
  }, function(response) {
    ok(false, "LIST");
  });

  // where
  // posts.where().get().then(function(response) {
  //   ok(response.length > 0 && response[response.length-1].title == "My awesome blog post", "LIST");
  // }, function(response) {
  //   ok(false, "LIST");
  // });

  start();
});
