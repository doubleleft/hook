asyncTest("Key-value Store", function() {
  client.keys.get('something').then(function(data) {
    console.log(data);
  });

  client.keys.set('something', 'data').then(function(data) {
    console.log(data);
  });

});

