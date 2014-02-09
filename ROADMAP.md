dl-api
===

Every API request should be sent with `X-App-Id` and `X-App-Key` headers, which is the credentials for the project.

**Collections**

- GET /api/collection/:name
- POST /api/collection/:name
- GET /api/collection/:name/:_id
- PUT /api/collection/:name/:_id
- PATCH /api/collection/:name/:_id

**Key/Value storage**

- GET /api/key/:name
- POST /api/key/:name
- PUT /api/key/:name
- PATCH /api/key/:name


**Authentication**

- GET /api/auth/:provider

# Yet to be done...
---


** Collection relationship **

- GET /api/collection/:name/:_id/:relation
- POST /api/collection/:name/:_id/:relation
- GET /api/collection/:name/:_id/:relation/:_id
- PUT /api/collection/:name/:_id/:relation/:_id
- PATCH /api/collection/:name/:_id/:relation/:_id


**Files**

- GET /api/files
- POST /api/files
- PUT /api/files/:_id
- GET /api/files/:_id

**Mailing**

- ?
- https://www.parse.com/docs/cloud_modules_guide#mailgun

**Batch (multiple requests)**

- https://developers.facebook.com/docs/graph-api/making-multiple-requests/

**Run under IIS / [Phalenger](https://github.com/DEVSENSE/Phalanger)**

- http://stackoverflow.com/questions/7178514/php-flush-stopped-flushing-in-iis7-5


Extra
---

**Tasks (?)**

- Queueing
- Background tasks

**Cache Layer**

- ?
