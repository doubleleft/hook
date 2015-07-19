changelog
===

v0.4.0
---

- improved relationship configs
- built-in collection security configs
- fixed returning strings on aggregated collection methods
- fixed duplicated 'lock_attributes' on schema cache
- fix auto-migrating collections on update calls
- allow to configure auth token expiration time on config.yaml
- fix output of 'keys' and 'modules' to CLI
- added template engine to be used with Module::template()->compile()
- added Context::unsafe() method to enforce unsafe collection operations.
- exposed orWhere method for client-side implementation
- allow 'falsy' default values on schema builder (#121)
- added handy App::url() method to generate urls with credentials. (#122)

v0.3.0
---

- fix auto-migrating on `update` / `update_multiple` calls
- `auth.token_expiration` in `config.yaml`

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
