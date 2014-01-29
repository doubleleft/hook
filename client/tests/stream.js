asyncTest("Server-Sent Events", function() {
  expect(1);

  var post_stream = client.collection('posts').stream({
    retry_timeout: 10,
    open: function(e) {
      console.log("opened: ", e);
    },
    error: function(e) {
      console.log("error: ", e);
    },
    message: function(data) {
      console.log("message: ", data);
    }
  });

  client.collection('posts').where('name', '=', 'frango').stream({
    message: function(data) {
      console.log("frango encontrado: ", data);
    }
  });

});


