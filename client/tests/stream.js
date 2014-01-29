asyncTest("Server-Sent Events", function() {
  expect(1);

  client.collection('streaming').create({
    name: "frango",
    age: "20"
  });

  client.collection('posts').where('age', '>=', '30').stream({
    retry_timeout: 10,
    open: function(e) { console.log("opened: ", e); },
    error: function(e) { console.log(e); },
    message: function(data) {
      ok(data.age >= 30);
    }
  });

  client.collection('posts').where('name', '=', 'frango').stream({
    message: function(data) {
      ok(data.name == "frango");
    }
  });

  client.collection('streaming').create({
    name: "frango",
    age: "20"
  });

  client.collection('streaming').create({
    name: "frango",
    age: "20"
  });

});
