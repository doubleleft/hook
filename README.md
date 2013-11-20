dl-api
===

**Draft implementation**

Take a look at the planned features below.

Web Interface
---

Features:

- create a new project
- handle api keys (per environment: production/development/testing)
- data viewer/administration per environment
- request analysis (middleware to track it)

API
---

Every API request should be sent with `X-Application-Id` and `X-Application-Key` headers.

**Collections**

- GET /api/collection/:name
- POST /api/collection/:name
- GET /api/collection/:name/:_id
- PUT /api/collection/:name/:_id
- PATCH /api/collection/:name/:_id

Collection relationship:

- GET /api/collection/:name/:_id/:relation
- POST /api/collection/:name/:_id/:relation
- GET /api/collection/:name/:_id/:relation/:_id
- PUT /api/collection/:name/:_id/:relation/:_id
- PATCH /api/collection/:name/:_id/:relation/:_id

**Key/Value storage**

- GET /api/key/:name
- POST /api/key/:name
- PUT /api/key/:name
- PATCH /api/key/:name

**Mailing**

- ?

**Authentication**

- GET /api/auth/:provider

**Files**

- GET /api/files
- POST /api/files
- PUT /api/files/:_id
- GET /api/files/:_id

**Batch (multiple requests)**

- https://developers.facebook.com/docs/graph-api/making-multiple-requests/


Extra
---

**Tasks (?)**

- Queueing
- Background tasks

**Cache**

- ?
