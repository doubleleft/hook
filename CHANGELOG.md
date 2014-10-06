dl-api changelog
===

v0.2.0:
---

- Schema definitions
- Cache support
- Relationship support (has_many / belongs_to / has_one)

v0.1.0:
---

- CRUD on `/collection/:name[/:id]`, supporting filters, ordering and limit/offset.
- Aggregation functions on collections: max, min, avg, sum, count
- Long pooling on collections, supporting filters and ordering.
- Authentication via `auth/email` and `auth/facebook` providers.
- Key/value storage
