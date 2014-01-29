asyncTest("Key-value Store", function() {
  expect(2);

  client.keys.get('something').then(function(data) {
    ok(true, "GET");
  });

  client.keys.set('something', 'data').then(function(data) {
    ok(data.value == "data", "SET");
    start();
  });

  setTimeout(function() {
  }, 200);

});

