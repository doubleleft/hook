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

  posts.sort('default');
  ok(posts.ordering[0][0] == 'default', "sort(), default field asc");
  ok(posts.ordering[0][1] == 'asc', "sort(), default orientation asc");

  posts.sort('field', 1);
  ok(posts.ordering[1][0] == 'field', "sort(), field asc");
  ok(posts.ordering[1][1] == 'asc', "sort(), orientation asc");

  posts.sort('field2', -1);
  ok(posts.ordering[2][0] == 'field2', "sort(), field desc");
  ok(posts.ordering[2][1] == 'desc', "sort(), orientation desc");

  posts.sort('field3', 'desc');
  ok(posts.ordering[3][0] == 'field3', "sort(), field desc");
  ok(posts.ordering[3][1] == 'desc', "sort(), orientation desc");

  posts.sort('field4', 'asc');
  ok(posts.ordering[4][0] == 'field4', "sort(), field asc");
  ok(posts.ordering[4][1] == 'asc', "sort(), orientation asc");
});
