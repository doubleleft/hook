test("Collection internals", function() {
  var posts = client.collection('posts');

  //
  // Wheres combinations
  //
  var past_date = (new Date()).getTime() - 24 * 60 * 60;
  posts.where('name', 'My awesome blog post');
  ok(posts.wheres.length==1, "where() field,value");

  posts.where('created_at', '>', past_date);
  ok(posts.wheres.length == 2, "where() field,operation,value");
  ok(posts.wheres[1][1] == ">", "where() field,operation,value");

  posts.reset();
  ok(posts.wheres.length == 0, "reset()");

  posts.where({
    name: "My awesome blog post",
    created_at: ['>', past_date],
  });

  ok(posts.wheres.length == 2, "where() object, test length");
  ok(posts.wheres[0][0] == "name", "where() object, test first field");
  ok(posts.wheres[0][1] == "=", "where() object, test first operation");
  ok(posts.wheres[0][2] == "My awesome blog post", "where() object, test first value");

  ok(posts.wheres[1][0] == "created_at", "where() object, test second field");
  ok(posts.wheres[1][1] == ">", "where() object, test second operation");
  ok(posts.wheres[1][2] == past_date, "where() object, test second value");
});
