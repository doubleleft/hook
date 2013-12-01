window.client = new DL.Client({
  appId: '1',
  key: "test"
});

test("API", function() {
  ok( client.url == "http://dl-api.dev/", "url OK");
  ok( client.appId == "1", "'appId' OK");
  ok( client.key == "test", "'secret' OK");
});
