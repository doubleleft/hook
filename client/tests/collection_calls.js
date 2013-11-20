asyncTest("Collection calls", function() {
  expect(7);

  var posts = client.collection('posts');
  var past_time = Math.round(((new Date).getTime() - 2000) / 1000);

  //
  // Create
  //
  posts.create({title: "My awesome blog post", content: "Lorem ipsum dolor sit amet."}).then(function(response) {
    ok(response.title ==  "My awesome blog post", "CREATE");
  }, function(response) {
    ok(false, "CREATE");
  });

  posts.create({string: "Another post", int: 5, float: 9.9, bool: true}).then(function(response) {
    ok(response.string ==  "Another post", "CREATE keep string data-type");
    ok(response.int == 5, "CREATE keep integer data-type");
    ok(response.float == 9.9, "CREATE keep float data-type");
    ok(response.bool === true, "CREATE keep boolean data-type");
  }, function(response) {
    ok(false, "CREATE");
  });

  //
  // Get without where
  //
  posts.get().then(function(response) {
    ok(response.length > 0 && response[response.length-1].string == "Another post", "LIST WITHOUT where");
  }, function(response) {
    ok(false, "LIST WITHOUT where");
  });

  //
  // Get with where
  //
  setTimeout(function() {
    posts.where({
      created_at: ['>', past_time]
    }).then(function(response) {
      debugger;
      ok(response.length == 2, "LIST WITH where, should retrieve 2 items");
    }, function(response) {
      debugger;
      ok(false, "LIST WITH where, should retrieve 2 items");
    });

    start();
  }, 200);

});

