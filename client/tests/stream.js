asyncTest("Server-Sent Events", function() {
  expect(1);

  client.collection('streaming').where('name', '=', 'frango').stream({
    message: function(data) {
      ok(data.name == "frango");
      this.close();
      start();
    }
  });

  client.collection('streaming').create({
    name: "frango",
    age: "20"
  });

});
