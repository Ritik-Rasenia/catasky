CATASKY — Compact OpenAPI & ERD Checklist

Purpose: quick developer-facing checklist for OpenAPI endpoints, ERD entities, and integration/ops notes.

1) Auth & Common
- POST /api/v1/auth/login — returns JWT, tenant context `subscriber_id` for subscribers, role for Super Admin.
- POST /api/v1/auth/refresh
- Middleware: `auth`, `role`, `permission`, `tenant` (enforces subscriber_id)
- Rate-limits per API key and per subscriber.

2) Subscriber (tenant) APIs
- GET /api/v1/admin/subscribers — (Super Admin) list with filters, pagination
- POST /api/v1/admin/subscribers — create subscriber
- PATCH /api/v1/admin/subscribers/{id}/status — approve/suspend/activate/deactivate
- GET /api/v1/admin/subscribers/{id}/workspace — access workspace data (admin only)

3) Category & Template APIs
- CRUD categories (admin-only)
  - GET /api/v1/admin/categories
  - POST /api/v1/admin/categories
  - PATCH /api/v1/admin/categories/{id}
  - DELETE /api/v1/admin/categories/{id}
- Template endpoints
  - GET /api/v1/admin/templates
  - POST /api/v1/admin/templates
  - Validation rules schema embedded in template

4) Attribute & Attribute Group APIs
- Admin manages attributes/groups/options
  - GET/POST/PATCH admin endpoints
- Key fields: `name`, `slug`, `type`, `options[]`, `group_id`, `required`, `filterable`, `comparable`, `variant_enabled`
- Approval flow: POST /api/v1/admin/attributes/pending -> approve/reject

5) Product APIs
- Subscriber product lifecycle (tenant-scoped)
  - GET /api/v1/subscriber/products
  - POST /api/v1/subscriber/products (save draft or submit)
  - PATCH /api/v1/subscriber/products/{id}
  - POST /api/v1/subscriber/products/{id}/submit
  - GET /api/v1/admin/products — admin review across tenants
  - PATCH /api/v1/admin/products/{id}/approve|reject|force-deactivate
- Bulk import APIs
  - POST /api/v1/subscriber/imports/products (returns job id)
  - GET /api/v1/subscriber/imports/{job_id}/status

6) Variant APIs
- Managed by subscriber but governed by template rules
  - POST /api/v1/subscriber/products/{id}/variants
  - PATCH /api/v1/subscriber/products/{id}/variants/{variant_id}
- Enforce SKU uniqueness: unique index `(subscriber_id, sku)` and unique sellable combination constraints.

7) Catalog APIs
- Subscriber catalogs
  - CRUD under /api/v1/subscriber/catalogs
  - Assign products: POST /api/v1/subscriber/catalogs/{id}/products
- Admin catalog templates and global catalogs under `/api/v1/admin/catalogs`.

8) Analytics, Logs & Reports
- Admin-only analytics endpoints for product counts, storage, API usage
- Logs: `/api/v1/admin/logs` with filters (activity, error, audit)

9) Jobs/Events
- Queue jobs: imports, image-processing, SEO-generation, notification emails
- Events: ProductSubmitted, ProductApproved, AttributePromoted, ImportCompleted
- Webhooks: optional per-subscriber for import completion and catalog exports

10) Responses & Errors
- Standard error format: `{ "code": "string", "message": "string", "details": {...} }`
- 401/403 for auth/perm failures, 422 for validation

11) Security
- All admin routes require Super Admin role
- Tenant isolation: every subscriber endpoint must require and validate `subscriber_id` from JWT or scoped API key
- Audit trail headers: `X-Request-Id`, `X-Actor-Id`, `X-Subscriber-Id`

12) Rate limiting & quotas
- Per-subscriber throttles and upload quotas. Admin can view/change quotas.

13) ERD — Core Entities (relations)
- Subscriber (1) — (N) Product
- Subscriber (1) — (N) Catalog
- Category (1) — (N) Template
- Template (1) — (N) AttributeGroup
- AttributeGroup (1) — (N) Attribute
- Category (1) — (N) AttributeAssignment (mapping table)
- Product (1) — (N) Variant
- Product (1) — (N) ProductMedia
- AttributeValue table: polymorphic values for `product_id`, `attribute_id`, `value` (typed/value_json)

14) ERD — Keys & Indexes (recommendations)
- Subscriber scoping: include `subscriber_id` on all tenant tables and compound indexes for list queries.
- Products: index `(subscriber_id, category_id, status, created_at)`
- Attributes: unique `(name, category_id)` or `(slug, category_id)` if allowed per category
- Variants: unique `(subscriber_id, sku)` and partial unique on sellable attribute combination
- Full-text/search index on product title, description, and selected searchable attributes

15) Search & Filter implementation notes
- Use search engine (Elasticsearch/Meilisearch/Typesense) for large catalogs
- Store attribute metadata to dynamically build filters; only include admin-marked filterable attributes
- Use aggregations for counts and filter facets

16) Migration & Versioning
- API versioning prefix `/api/v1/...`
- Templates and attribute model changes require migration scripts with backward compatibility strategies (feature flags, phased migrations)

17) Developer deliverables
- Generate minimal OpenAPI skeleton for the above endpoints
- ERD diagram (Mermaid or image) representing core entities
- Seeders for base categories, attribute groups, and critical templates

---
Next steps: generate an OpenAPI skeleton and a Mermaid ERD file if you want — shall I create those now?