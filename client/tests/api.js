window.client = new DL.Client({
  appId: '528a0b68773db8ac10b7acd9',
  key: "S7ggGEOfBmbyO+nBll+OvBONnYUQ\/QhVLIrGpWcgRtE="
});

test("API", function() {
  ok( client.url == "http://dl-api.dev/", "url OK");
  ok( client.appId == "528a0b68773db8ac10b7acd9", "'appId' OK");
  ok( client.key == "S7ggGEOfBmbyO+nBll+OvBONnYUQ\/QhVLIrGpWcgRtE=", "'secret' OK");
});
