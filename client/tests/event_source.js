asyncTest("Server-Sent Events", function() {
  expect(1);

  var es = new EventSource(window.client.url + "collection/posts?" + 'X-App-Id=' + window.client.appId + "&X-App-Key=" + window.client.key, {
    withCredentials: true
  });
  es.onopen = function(e) {
    console.log("opened: ", e);
    ok(true);
  };
  es.onerror = function(e) {
    console.log("error: ", e);
  };
  es.onmessage = function(e) {
    console.log("message: ", e);

  }

});


