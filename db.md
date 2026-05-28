# ERP — Database Schema

> **Total Migrations:** ±342 files | **Total Tables:** ±180 entitas

---

## 1. CORE SYSTEM (database/migrations/)

### 1.1. `users`

| Column                            | Type                                | Constraint                       |
| --------------------------------- | ----------------------------------- | -------------------------------- |
| id                                | bigint, auto                        | PK                               |
| name                              | string(255)                         |                                  |
| email                             | string(255)                         | UNIQUE                           |
| email_verified_at                 | timestamp                           | nullable                         |
| language                          | string(255)                         | nullable                         |
| is_active                         | boolean                             | default true                     |
| password                          | string(255)                         |                                  |
| remember_token                    | string(100)                         | nullable                         |
| default_company_id                | bigint                              | FK → companies, nullable         |
| partner_id                        | bigint                              | FK → partners_partners, nullable |
| creator_id                        | bigint                              | FK → users, nullable             |
| is_default                        | boolean                             | default false                    |
| resource_permission               | enum('global','group','individual') | default 'individual'             |
| app_authentication_secret         | text                                | nullable                         |
| app_authentication_recovery_codes | text                                | nullable                         |
| has_email_authentication          | boolean                             | default false                    |
| deleted_at                        | timestamp                           | nullable (softDeletes)           |
| created_at, updated_at            | timestamp                           |                                  |

### 1.2. `password_reset_tokens`

| Column     | Type        | Constraint |
| ---------- | ----------- | ---------- |
| email      | string(255) | PK         |
| token      | string(255) |            |
| created_at | timestamp   | nullable   |

### 1.3. `sessions`

| Column        | Type        | Constraint                    |
| ------------- | ----------- | ----------------------------- |
| id            | string(255) | PK                            |
| user_id       | bigint      | FK → users, nullable, indexed |
| ip_address    | string(45)  | nullable                      |
| user_agent    | text        | nullable                      |
| payload       | longText    |                               |
| last_activity | integer     | indexed                       |

### 1.4. `cache`

| Column     | Type        | Constraint |
| ---------- | ----------- | ---------- |
| key        | string(255) | PK         |
| value      | mediumText  |            |
| expiration | integer     |            |

### 1.5. `cache_locks`

| Column     | Type        | Constraint |
| ---------- | ----------- | ---------- |
| key        | string(255) | PK         |
| owner      | string(255) |            |
| expiration | integer     |            |

### 1.6. `jobs`

| Column       | Type                | Constraint |
| ------------ | ------------------- | ---------- |
| id           | bigint, auto        | PK         |
| queue        | string(255)         | indexed    |
| payload      | longText            |            |
| attempts     | unsignedTinyInteger |            |
| reserved_at  | unsignedInteger     | nullable   |
| available_at | unsignedInteger     |            |
| created_at   | unsignedInteger     |            |

### 1.7. `job_batches`

| Column         | Type        | Constraint |
| -------------- | ----------- | ---------- |
| id             | string(255) | PK         |
| name           | string(255) |            |
| total_jobs     | integer     |            |
| pending_jobs   | integer     |            |
| failed_jobs    | integer     |            |
| failed_job_ids | longText    |            |
| options        | mediumText  | nullable   |
| cancelled_at   | integer     | nullable   |
| created_at     | integer     |            |
| finished_at    | integer     | nullable   |

### 1.8. `failed_jobs`

| Column     | Type         | Constraint |
| ---------- | ------------ | ---------- |
| id         | bigint, auto | PK         |
| uuid       | string(255)  | UNIQUE     |
| connection | text         |            |
| queue      | text         |            |
| payload    | longText     |            |
| exception  | longText     |            |
| failed_at  | timestamp    | useCurrent |

### 1.9. `settings`

| Column                 | Type         | Constraint         |
| ---------------------- | ------------ | ------------------ |
| id                     | bigint, auto | PK                 |
| group                  | string(255)  | UNIQUE(group,name) |
| name                   | string(255)  | UNIQUE(group,name) |
| locked                 | boolean      | default false      |
| payload                | json         |                    |
| created_at, updated_at | timestamp    |                    |

### 1.10. `permissions`

| Column                 | Type         | Constraint              |
| ---------------------- | ------------ | ----------------------- |
| id                     | bigint, auto | PK                      |
| name                   | string(255)  | UNIQUE(name,guard_name) |
| guard_name             | string(255)  | UNIQUE(name,guard_name) |
| created_at, updated_at | timestamp    |                         |

### 1.11. `roles`

| Column                 | Type         | Constraint              |
| ---------------------- | ------------ | ----------------------- |
| id                     | bigint, auto | PK                      |
| is_default             | boolean      | default false           |
| name                   | string(255)  | UNIQUE(name,guard_name) |
| guard_name             | string(255)  | UNIQUE(name,guard_name) |
| created_at, updated_at | timestamp    |                         |

### 1.12. `model_has_permissions`

| Column          | Type        | Constraint           |
| --------------- | ----------- | -------------------- |
| permission_id   | bigint      | FK → permissions, PK |
| model_type      | string(255) | PK                   |
| model_morph_key | bigint      | PK, indexed          |

### 1.13. `model_has_roles`

| Column          | Type        | Constraint     |
| --------------- | ----------- | -------------- |
| role_id         | bigint      | FK → roles, PK |
| model_type      | string(255) | PK             |
| model_morph_key | bigint      | PK, indexed    |

### 1.14. `role_has_permissions`

| Column        | Type   | Constraint           |
| ------------- | ------ | -------------------- |
| permission_id | bigint | FK → permissions, PK |
| role_id       | bigint | FK → roles, PK       |

### 1.15. `imports`

| Column                 | Type            | Constraint                  |
| ---------------------- | --------------- | --------------------------- |
| id                     | bigint, auto    | PK                          |
| completed_at           | timestamp       | nullable                    |
| file_name              | string(255)     |                             |
| file_path              | string(255)     |                             |
| importer               | string(255)     |                             |
| processed_rows         | unsignedInteger | default 0                   |
| total_rows             | unsignedInteger |                             |
| successful_rows        | unsignedInteger | default 0                   |
| user_id                | bigint          | FK → users, cascadeOnDelete |
| created_at, updated_at | timestamp       |                             |

### 1.16. `exports`

| Column                 | Type            | Constraint                  |
| ---------------------- | --------------- | --------------------------- |
| id                     | bigint, auto    | PK                          |
| completed_at           | timestamp       | nullable                    |
| file_disk              | string(255)     |                             |
| file_name              | string(255)     | nullable                    |
| exporter               | string(255)     |                             |
| processed_rows         | unsignedInteger | default 0                   |
| total_rows             | unsignedInteger |                             |
| successful_rows        | unsignedInteger | default 0                   |
| user_id                | bigint          | FK → users, cascadeOnDelete |
| created_at, updated_at | timestamp       |                             |

### 1.17. `failed_import_rows`

| Column                 | Type         | Constraint                    |
| ---------------------- | ------------ | ----------------------------- |
| id                     | bigint, auto | PK                            |
| data                   | json         |                               |
| import_id              | bigint       | FK → imports, cascadeOnDelete |
| validation_error       | text         | nullable                      |
| created_at, updated_at | timestamp    |                               |

### 1.18. `notifications`

| Column                 | Type        | Constraint |
| ---------------------- | ----------- | ---------- |
| id                     | uuid        | PK         |
| type                   | string(255) |            |
| notifiable_type        | string(255) | indexed    |
| notifiable_id          | bigint      | indexed    |
| data                   | text        |            |
| read_at                | timestamp   | nullable   |
| created_at, updated_at | timestamp   |            |

### 1.19. `personal_access_tokens`

| Column                 | Type         | Constraint        |
| ---------------------- | ------------ | ----------------- |
| id                     | bigint, auto | PK                |
| tokenable_type         | string(255)  | indexed           |
| tokenable_id           | bigint       | indexed           |
| name                   | text         |                   |
| token                  | string(64)   | UNIQUE            |
| abilities              | text         | nullable          |
| last_used_at           | timestamp    | nullable          |
| expires_at             | timestamp    | nullable, indexed |
| created_at, updated_at | timestamp    |                   |

---

## 2. PLUGIN MANAGER (plugin-manager)

### 2.1. `plugins`

| Column                 | Type         | Constraint   |
| ---------------------- | ------------ | ------------ |
| id                     | bigint, auto | PK           |
| name                   | string(255)  |              |
| is_active              | boolean      | default true |
| created_at, updated_at | timestamp    |              |

---

## 3. SUPPORT PLUGIN (Webkul\Support)

### 3.1. `plugin_dependencies`

| Column        | Type   | Constraint                                  |
| ------------- | ------ | ------------------------------------------- |
| plugin_id     | bigint | FK → plugins, cascadeOnDelete, UNIQUE(pair) |
| dependency_id | bigint | FK → plugins, cascadeOnDelete, UNIQUE(pair) |

### 3.2. `currencies`

| Column                 | Type         | Constraint   |
| ---------------------- | ------------ | ------------ |
| id                     | bigint, auto | PK           |
| name                   | string(255)  |              |
| symbol                 | string(255)  | nullable     |
| iso_numeric            | integer      | nullable     |
| decimal_places         | tinyInteger  | nullable     |
| full_name              | string(255)  | nullable     |
| rounding               | decimal(8,2) | default 0.00 |
| active                 | boolean      | default true |
| created_at, updated_at | timestamp    |              |

### 3.3. `countries`

| Column                 | Type         | Constraint                |
| ---------------------- | ------------ | ------------------------- |
| id                     | bigint, auto | PK                        |
| currency_id            | bigint       | FK → currencies, nullable |
| phone_code             | string(255)  | nullable                  |
| code                   | string(2)    | nullable                  |
| name                   | string(255)  | nullable                  |
| state_required         | boolean      | default false             |
| zip_required           | boolean      | default false             |
| created_at, updated_at | timestamp    |                           |

### 3.4. `companies`

| Column                 | Type         | Constraint                                |
| ---------------------- | ------------ | ----------------------------------------- |
| id                     | bigint, auto | PK                                        |
| parent_id              | bigint       | FK → companies, nullable, cascadeOnDelete |
| currency_id            | bigint       | FK → currencies, nullable                 |
| creator_id             | bigint       | FK → users, nullable                      |
| partner_id             | bigint       | FK → partners_partners, nullable          |
| sort                   | integer      | nullable                                  |
| name                   | string(255)  | UNIQUE                                    |
| company_id             | string(255)  | UNIQUE, nullable                          |
| tax_id                 | string(255)  | UNIQUE, nullable                          |
| registration_number    | string(255)  | nullable                                  |
| email                  | string(255)  | nullable                                  |
| phone                  | string(255)  | nullable                                  |
| mobile                 | string(255)  | nullable                                  |
| website                | string(255)  | nullable                                  |
| street1                | string(255)  | nullable                                  |
| street2                | string(255)  | nullable                                  |
| city                   | string(255)  | nullable                                  |
| zip                    | string(255)  | nullable                                  |
| state_id               | bigint       | FK → states, nullable                     |
| country_id             | bigint       | FK → countries, nullable                  |
| color                  | string(255)  | nullable                                  |
| is_active              | boolean      | default true                              |
| founded_date           | date         | nullable                                  |
| deleted_at             | timestamp    | nullable                                  |
| created_at, updated_at | timestamp    |                                           |

### 3.5. `states`

| Column                 | Type         | Constraint                      |
| ---------------------- | ------------ | ------------------------------- |
| id                     | bigint, auto | PK                              |
| country_id             | bigint       | FK → countries, cascadeOnDelete |
| name                   | string(255)  |                                 |
| code                   | string(255)  |                                 |
| created_at, updated_at | timestamp    |                                 |

### 3.6. `user_allowed_companies`

| Column     | Type         | Constraint                      |
| ---------- | ------------ | ------------------------------- |
| id         | bigint, auto | PK                              |
| user_id    | bigint       | FK → users, cascadeOnDelete     |
| company_id | bigint       | FK → companies, cascadeOnDelete |

### 3.7. `banks`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  | nullable                 |
| code                   | string(255)  | nullable                 |
| email                  | string(255)  | nullable                 |
| phone                  | string(255)  | nullable                 |
| street1                | string(255)  | nullable                 |
| street2                | string(255)  | nullable                 |
| city                   | string(255)  | nullable                 |
| zip                    | string(255)  | nullable                 |
| state_id               | bigint       | FK → states, nullable    |
| country_id             | bigint       | FK → countries, nullable |
| creator_id             | bigint       | FK → users               |
| deleted_at             | timestamp    | nullable                 |
| created_at, updated_at | timestamp    |                          |

### 3.8. `activity_plans`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| plugin                 | string(255)  | nullable                             |
| name                   | string(255)  |                                      |
| department_id          | bigint       | FK → employees_departments, nullable |
| is_active              | boolean      | default false                        |
| creator_id             | bigint       | FK → users, nullable                 |
| company_id             | bigint       | FK → companies, nullable             |
| deleted_at             | timestamp    | nullable                             |
| created_at, updated_at | timestamp    |                                      |

### 3.9. `activity_types`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| sort                   | integer      | nullable                             |
| delay_count            | integer      | nullable                             |
| delay_unit             | string(255)  |                                      |
| delay_from             | string(255)  |                                      |
| icon                   | string(255)  | nullable                             |
| decoration_type        | string(255)  | nullable                             |
| chaining_type          | string(255)  | default 'suggest'                    |
| plugin                 | string(255)  | nullable                             |
| category               | string(255)  | nullable                             |
| name                   | string(255)  |                                      |
| summary                | text         | nullable                             |
| default_note           | text         | nullable                             |
| is_active              | boolean      | default true                         |
| keep_done              | boolean      | default false                        |
| creator_id             | bigint       | FK → users, nullable                 |
| default_user_id        | bigint       | FK → users, nullable                 |
| activity_plan_id       | bigint       | FK → activity_plans, cascadeOnDelete |
| triggered_next_type_id | bigint       | FK → activity_types                  |
| deleted_at             | timestamp    | nullable                             |
| created_at, updated_at | timestamp    |                                      |

### 3.10. `activity_plan_templates`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| sort                   | integer      | nullable                             |
| plan_id                | bigint       | FK → activity_plans, cascadeOnDelete |
| activity_type_id       | bigint       | FK → activity_types                  |
| responsible_id         | bigint       | FK → users, nullable                 |
| creator_id             | bigint       | FK → users, nullable                 |
| delay_count            | integer      | nullable                             |
| delay_unit             | string(255)  |                                      |
| delay_from             | string(255)  |                                      |
| summary                | text         | nullable                             |
| responsible_type       | string(255)  |                                      |
| note                   | text         | nullable                             |
| created_at, updated_at | timestamp    |                                      |

### 3.11. `activity_type_suggestions`

| Column                     | Type   | Constraint                           |
| -------------------------- | ------ | ------------------------------------ |
| activity_type_id           | bigint | FK → activity_types, cascadeOnDelete |
| suggested_activity_type_id | bigint | FK → activity_types, cascadeOnDelete |

### 3.12. `email_logs`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| recipient_email        | string(255)  |            |
| recipient_name         | string(255)  |            |
| subject                | string(255)  |            |
| status                 | string(255)  |            |
| error_message          | text         | nullable   |
| sent_at                | timestamp    |            |
| created_at, updated_at | timestamp    |            |

### 3.13. `unit_of_measure_categories`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 3.14. `unit_of_measures`

| Column                 | Type          | Constraint                      |
| ---------------------- | ------------- | ------------------------------- |
| id                     | bigint, auto  | PK                              |
| type                   | string(255)   |                                 |
| name                   | string(255)   |                                 |
| factor                 | double        | nullable, default 0             |
| rounding               | decimal(15,4) | nullable, default 0             |
| category_id            | bigint        | FK → unit_of_measure_categories |
| creator_id             | bigint        | FK → users, nullable            |
| deleted_at             | timestamp     | nullable                        |
| created_at, updated_at | timestamp     |                                 |

### 3.15. `utm_mediums`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| creator_id             | bigint       | FK → users, nullable |
| name                   | string(255)  |                      |
| created_at, updated_at | timestamp    |                      |

### 3.16. `utm_sources`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| creator_id             | bigint       | FK → users, nullable |
| name                   | string(255)  |                      |
| created_at, updated_at | timestamp    |                      |

### 3.17. `utm_stages`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| sort                   | integer      | nullable             |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 3.18. `utm_campaigns`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| user_id                | bigint       | FK → users               |
| stage_id               | bigint       | FK → utm_stages          |
| color                  | string(255)  | nullable                 |
| creator_id             | bigint       | FK → users, nullable     |
| name                   | string(255)  |                          |
| title                  | string(255)  |                          |
| is_active              | boolean      | default false            |
| is_auto_campaign       | boolean      | default false            |
| company_id             | bigint       | FK → companies, nullable |
| created_at, updated_at | timestamp    |                          |

### 3.19. `currency_rates`

| Column                 | Type          | Constraint                       |
| ---------------------- | ------------- | -------------------------------- |
| id                     | bigint, auto  | PK                               |
| name                   | date          |                                  |
| rate                   | decimal(15,6) | default 1.000000                 |
| currency_id            | bigint        | FK → currencies, cascadeOnDelete |
| creator_id             | bigint        | FK → users, nullable             |
| company_id             | bigint        | FK → companies, nullable         |
| created_at, updated_at | timestamp     |                                  |

### 3.20. `calendars`

| Column                   | Type         | Constraint               |
| ------------------------ | ------------ | ------------------------ |
| id                       | bigint, auto | PK                       |
| name                     | string(255)  |                          |
| timezone                 | string(255)  |                          |
| hours_per_day            | float        | nullable                 |
| is_active                | boolean      | default false            |
| two_weeks_calendar       | boolean      | nullable                 |
| flexible_hours           | boolean      | nullable                 |
| full_time_required_hours | float        | nullable                 |
| creator_id               | bigint       | FK → users, nullable     |
| company_id               | bigint       | FK → companies, nullable |
| deleted_at               | timestamp    | nullable                 |
| created_at, updated_at   | timestamp    |                          |

### 3.21. `calendar_attendances`

| Column                 | Type         | Constraint                      |
| ---------------------- | ------------ | ------------------------------- |
| id                     | bigint, auto | PK                              |
| sort                   | integer      | nullable                        |
| name                   | string(255)  |                                 |
| day_of_week            | string(255)  |                                 |
| day_period             | string(255)  |                                 |
| week_type              | string(255)  | nullable                        |
| display_type           | string(255)  | nullable                        |
| date_from              | string(255)  | nullable                        |
| date_to                | string(255)  | nullable                        |
| duration_days          | string(255)  | nullable                        |
| hour_from              | string(255)  |                                 |
| hour_to                | string(255)  |                                 |
| calendar_id            | bigint       | FK → calendars, cascadeOnDelete |
| creator_id             | bigint       | FK → users, nullable            |
| resource_type          | string(255)  | nullable (morph)                |
| resource_id            | bigint       | nullable (morph)                |
| created_at, updated_at | timestamp    |                                 |

### 3.22. `calendar_leaves`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| time_type              | string(255)  |                          |
| date_from              | string(255)  |                          |
| date_to                | string(255)  |                          |
| company_id             | bigint       | FK → companies, nullable |
| calendar_id            | bigint       | FK → calendars, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| resource_type          | string(255)  | nullable (morph)         |
| resource_id            | bigint       | nullable (morph)         |
| created_at, updated_at | timestamp    |                          |

---

## 4. SECURITY PLUGIN (Webkul\Security)

### 4.1. `user_invitations`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| email                  | string(255)  |            |
| created_at, updated_at | timestamp    |            |

### 4.2. `teams`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| creator_id             | bigint       | FK → users, nullable |
| name                   | string(255)  |                      |
| created_at, updated_at | timestamp    |                      |

### 4.3. `user_team`

| Column  | Type   | Constraint                  |
| ------- | ------ | --------------------------- |
| user_id | bigint | FK → users, cascadeOnDelete |
| team_id | bigint | FK → teams, cascadeOnDelete |

---

## 5. CHATTER PLUGIN (Webkul\Chatter)

### 5.1. `chatter_followers`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| user_id                | bigint       | FK → users |
| followable_type        | string(255)  |            |
| followable_id          | bigint       |            |
| created_at, updated_at | timestamp    |            |

### 5.2. `chatter_messages`

| Column                 | Type         | Constraint    |
| ---------------------- | ------------ | ------------- |
| id                     | bigint, auto | PK            |
| body                   | text         |               |
| author_id              | bigint       | FK → users    |
| messageable_type       | string(255)  |               |
| messageable_id         | bigint       |               |
| is_read                | boolean      | default false |
| created_at, updated_at | timestamp    |               |

### 5.3. `chatter_attachments`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| filename               | string(255)  |            |
| path                   | string(255)  |            |
| mime_type              | string(255)  |            |
| size                   | integer      |            |
| attachmentable_type    | string(255)  |            |
| attachmentable_id      | bigint       |            |
| created_at, updated_at | timestamp    |            |

---

## 6. PARTNERS PLUGIN (Webkul\Partner)

### 6.1. `partners_industries`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  | UNIQUE               |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 6.2. `partners_titles`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  | UNIQUE               |
| short_name             | string(255)  | nullable             |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 6.3. `partners_partners`

| Column                            | Type          | Constraint                                        |
| --------------------------------- | ------------- | ------------------------------------------------- |
| id                                | bigint, auto  | PK                                                |
| title_id                          | bigint        | FK → partners_titles, nullable                    |
| industry_id                       | bigint        | FK → partners_industries, nullable                |
| company_id                        | bigint        | FK → companies, nullable                          |
| user_id                           | bigint        | FK → users, nullable                              |
| creator_id                        | bigint        | FK → users, nullable                              |
| parent_id                         | bigint        | FK → partners_partners, nullable                  |
| name                              | string(255)   |                                                   |
| email                             | string(255)   | nullable                                          |
| phone                             | string(255)   | nullable                                          |
| mobile                            | string(255)   | nullable                                          |
| website                           | string(255)   | nullable                                          |
| function                          | string(255)   | nullable (job position)                           |
| is_company                        | boolean       | default false                                     |
| street1                           | string(255)   | nullable                                          |
| street2                           | string(255)   | nullable                                          |
| city                              | string(255)   | nullable                                          |
| zip                               | string(255)   | nullable                                          |
| state_id                          | bigint        | FK → states, nullable                             |
| country_id                        | bigint        | FK → countries, nullable                          |
| color                             | integer       | nullable                                          |
| is_active                         | boolean       | default true                                      |
| image                             | json          | nullable                                          |
| partner_type                      | string(255)   | nullable ('contact','invoice','shipping','other') |
| **Accounting columns:**           |               |                                                   |
| message_bounce                    | integer       | nullable                                          |
| supplier_rank                     | integer       | nullable                                          |
| customer_rank                     | integer       | nullable                                          |
| invoice_warning                   | string(255)   | nullable                                          |
| credit_limit                      | string(255)   | nullable                                          |
| trust                             | integer       | nullable                                          |
| debit_limit                       | decimal(16,2) | nullable                                          |
| property_account_payable_id       | bigint        | FK → accounts_accounts, nullable                  |
| property_account_receivable_id    | bigint        | FK → accounts_accounts, nullable                  |
| property_account_position_id      | bigint        | FK → accounts_fiscal_positions, nullable          |
| property_payment_term_id          | bigint        | FK → accounts_payment_terms, nullable             |
| property_supplier_payment_term_id | bigint        | FK → accounts_payment_terms, nullable             |
| comment                           | text          | nullable                                          |
| **Website columns:**              |               |                                                   |
| password                          | string(255)   | nullable                                          |
| email_verified_at                 | timestamp     | nullable                                          |
| remember_token                    | string(100)   | nullable                                          |
| deleted_at                        | timestamp     | nullable                                          |
| created_at, updated_at            | timestamp     |                                                   |

### 6.4. `partners_bank_accounts`

| Column                 | Type         | Constraint                              |
| ---------------------- | ------------ | --------------------------------------- |
| id                     | bigint, auto | PK                                      |
| partner_id             | bigint       | FK → partners_partners, cascadeOnDelete |
| bank_id                | bigint       | FK → banks, nullable                    |
| acc_number             | string(255)  |                                         |
| acc_holder_name        | string(255)  | nullable                                |
| currency_id            | bigint       | FK → currencies, nullable               |
| creator_id             | bigint       | FK → users, nullable                    |
| created_at, updated_at | timestamp    |                                         |

### 6.5. `partners_tags`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| name                   | string(255)  |            |
| color                  | string(255)  | nullable   |
| created_at, updated_at | timestamp    |            |

### 6.6. `partners_partner_tag`

| Column     | Type   | Constraint                              |
| ---------- | ------ | --------------------------------------- |
| partner_id | bigint | FK → partners_partners, cascadeOnDelete |
| tag_id     | bigint | FK → partners_tags, cascadeOnDelete     |

---

## 7. PRODUCTS PLUGIN (Webkul\Product)

### 7.1. `products_categories`

| Column                           | Type         | Constraint                         |
| -------------------------------- | ------------ | ---------------------------------- |
| id                               | bigint, auto | PK                                 |
| name                             | string(255)  | indexed                            |
| full_name                        | string(255)  | nullable                           |
| parent_path                      | string(255)  | nullable                           |
| parent_id                        | bigint       | FK → products_categories, nullable |
| product_properties_definition    | json         | nullable                           |
| property_account_income_id       | bigint       | FK → accounts_accounts, nullable   |
| property_account_expense_id      | bigint       | FK → accounts_accounts, nullable   |
| property_account_down_payment_id | bigint       | FK → accounts_accounts, nullable   |
| creator_id                       | bigint       | FK → users, nullable               |
| created_at, updated_at           | timestamp    |                                    |

### 7.2. `products_products`

| Column                 | Type          | Constraint                                |
| ---------------------- | ------------- | ----------------------------------------- |
| id                     | bigint, auto  | PK                                        |
| type                   | string(255)   | ('goods','service','consu')               |
| name                   | string(255)   |                                           |
| service_tracking       | string(255)   | default 'none'                            |
| reference              | string(255)   | nullable                                  |
| barcode                | string(255)   | nullable                                  |
| price                  | decimal(15,4) | nullable                                  |
| cost                   | decimal(15,4) | nullable                                  |
| volume                 | decimal(15,4) | nullable                                  |
| weight                 | decimal(15,4) | nullable                                  |
| description            | text          | nullable                                  |
| description_purchase   | text          | nullable                                  |
| description_sale       | text          | nullable                                  |
| enable_sales           | boolean       | nullable                                  |
| enable_purchase        | boolean       | nullable                                  |
| is_favorite            | boolean       | default false                             |
| is_configurable        | boolean       | nullable                                  |
| sort                   | integer       | nullable                                  |
| images                 | json          | nullable                                  |
| parent_id              | bigint        | FK → products_products, nullable          |
| uom_id                 | bigint        | FK → unit_of_measures                     |
| uom_po_id              | bigint        | FK → unit_of_measures                     |
| category_id            | bigint        | FK → products_categories, cascadeOnDelete |
| company_id             | bigint        | FK → companies, nullable                  |
| creator_id             | bigint        | FK → users, nullable                      |
| **Inventory columns:** |               |                                           |
| shelf_location         | string(255)   | nullable                                  |
| location_type          | string(255)   | nullable                                  |
| tracking               | string(255)   | nullable                                  |
| weight_uom_id          | bigint        | FK → unit_of_measures, nullable           |
| volume_uom_id          | bigint        | FK → unit_of_measures, nullable           |
| deleted_at             | timestamp     | nullable                                  |
| created_at, updated_at | timestamp     |                                           |

### 7.3. `products_tags`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  | UNIQUE               |
| color                  | string(255)  | nullable             |
| creator_id             | bigint       | FK → users, nullable |
| deleted_at             | timestamp    | nullable             |
| created_at, updated_at | timestamp    |                      |

### 7.4. `products_product_tag` (pivot)

| Column     | Type   | Constraint                              |
| ---------- | ------ | --------------------------------------- |
| tag_id     | bigint | FK → products_tags, cascadeOnDelete     |
| product_id | bigint | FK → products_products, cascadeOnDelete |

### 7.5. `products_attributes`

| Column                 | Type         | Constraint                         |
| ---------------------- | ------------ | ---------------------------------- |
| id                     | bigint, auto | PK                                 |
| name                   | string(255)  |                                    |
| type                   | string(255)  | ('select','radio','color','multi') |
| sort                   | integer      | nullable                           |
| creator_id             | bigint       | FK → users, nullable               |
| deleted_at             | timestamp    | nullable                           |
| created_at, updated_at | timestamp    |                                    |

### 7.6. `products_attribute_options`

| Column                 | Type          | Constraint                                |
| ---------------------- | ------------- | ----------------------------------------- |
| id                     | bigint, auto  | PK                                        |
| name                   | string(255)   |                                           |
| color                  | string(255)   | nullable                                  |
| extra_price            | decimal(15,4) | nullable                                  |
| sort                   | integer       | nullable                                  |
| attribute_id           | bigint        | FK → products_attributes, cascadeOnDelete |
| creator_id             | bigint        | FK → users, nullable                      |
| created_at, updated_at | timestamp     |                                           |

### 7.7. `products_product_attributes`

| Column                 | Type         | Constraint                                |
| ---------------------- | ------------ | ----------------------------------------- |
| id                     | bigint, auto | PK                                        |
| sort                   | integer      | nullable                                  |
| product_id             | bigint       | FK → products_products, cascadeOnDelete   |
| attribute_id           | bigint       | FK → products_attributes, cascadeOnDelete |
| creator_id             | bigint       | FK → users, nullable                      |
| created_at, updated_at | timestamp    |                                           |

### 7.8. `products_product_attribute_values`

| Column               | Type          | Constraint                                        |
| -------------------- | ------------- | ------------------------------------------------- |
| id                   | bigint, auto  | PK                                                |
| extra_price          | decimal(15,4) | nullable                                          |
| product_id           | bigint        | FK → products_products, nullable                  |
| attribute_id         | bigint        | FK → products_attributes, nullable                |
| product_attribute_id | bigint        | FK → products_product_attributes, cascadeOnDelete |
| attribute_option_id  | bigint        | FK → products_attribute_options, cascadeOnDelete  |

### 7.9. `products_packagings`

| Column                 | Type          | Constraint                               |
| ---------------------- | ------------- | ---------------------------------------- |
| id                     | bigint, auto  | PK                                       |
| name                   | string(255)   |                                          |
| barcode                | string(255)   | nullable                                 |
| qty                    | decimal(12,4) | nullable                                 |
| sort                   | integer       | nullable                                 |
| product_id             | bigint        | FK → products_products, cascadeOnDelete  |
| package_type_id        | bigint        | FK → inventories_package_types, nullable |
| creator_id             | bigint        | FK → users, nullable                     |
| company_id             | bigint        | FK → companies, nullable                 |
| created_at, updated_at | timestamp     |                                          |

### 7.10. `products_price_rules`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| sort                   | integer      | nullable                 |
| currency_id            | bigint       | FK → currencies          |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| deleted_at             | timestamp    | nullable                 |
| created_at, updated_at | timestamp    |                          |

### 7.11. `products_price_rule_items`

| Column                 | Type          | Constraint                                 |
| ---------------------- | ------------- | ------------------------------------------ |
| id                     | bigint, auto  | PK                                         |
| apply_to               | string(255)   |                                            |
| display_apply_to       | string(255)   |                                            |
| base                   | string(255)   |                                            |
| type                   | string(255)   |                                            |
| min_quantity           | decimal(15,4) | default 0                                  |
| fixed_price            | decimal(15,4) | default 0                                  |
| price_discount         | decimal(15,4) | default 0                                  |
| price_round            | decimal(15,4) | default 0                                  |
| price_surcharge        | decimal(15,4) | default 0                                  |
| price_markup           | decimal(15,4) | default 0                                  |
| price_min_margin       | decimal(15,4) | default 0                                  |
| percent_price          | decimal(15,4) | default 0                                  |
| starts_at              | datetime      | nullable                                   |
| ends_at                | datetime      | nullable                                   |
| price_rule_id          | bigint        | FK → products_price_rules, cascadeOnDelete |
| base_price_rule_id     | bigint        | FK → products_price_rules, nullable        |
| product_id             | bigint        | FK → products_products, cascadeOnDelete    |
| category_id            | bigint        | FK → products_categories, cascadeOnDelete  |
| currency_id            | bigint        | FK → currencies, nullable                  |
| company_id             | bigint        | FK → companies, nullable                   |
| creator_id             | bigint        | FK → users, nullable                       |
| created_at, updated_at | timestamp     |                                            |

### 7.12. `products_product_suppliers`

| Column                 | Type          | Constraint                              |
| ---------------------- | ------------- | --------------------------------------- |
| id                     | bigint, auto  | PK                                      |
| sort                   | integer       | nullable                                |
| delay                  | integer       | default 0 (lead time days)              |
| product_name           | string(255)   | nullable                                |
| product_code           | string(255)   | nullable                                |
| starts_at              | date          | nullable                                |
| ends_at                | date          | nullable                                |
| min_qty                | decimal(12,4) | default 0                               |
| price                  | decimal(15,4) | default 0                               |
| discount               | decimal(15,4) | default 0                               |
| price_discounted       | decimal(15,4) | default 0                               |
| product_id             | bigint        | FK → products_products, nullable        |
| partner_id             | bigint        | FK → partners_partners, cascadeOnDelete |
| currency_id            | bigint        | FK → currencies                         |
| company_id             | bigint        | FK → companies, nullable                |
| creator_id             | bigint        | FK → users, nullable                    |
| uom_id                 | bigint        | FK → unit_of_measures, nullable         |
| created_at, updated_at | timestamp     |                                         |

### 7.13. `products_product_price_lists`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| sort                   | integer      | nullable                 |
| name                   | string(255)  |                          |
| is_active              | boolean      | default true             |
| currency_id            | bigint       | FK → currencies          |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| created_at, updated_at | timestamp    |                          |

### 7.14. `products_product_combinations`

| Column                     | Type         | Constraint                                              |
| -------------------------- | ------------ | ------------------------------------------------------- |
| id                         | bigint, auto | PK                                                      |
| product_id                 | bigint       | FK → products_products, cascadeOnDelete                 |
| product_attribute_value_id | bigint       | FK → products_product_attribute_values, cascadeOnDelete |
| created_at, updated_at     | timestamp    |                                                         |

---

## 8. SALES PLUGIN (Webkul\Sale)

### 8.1. `sales_teams`

| Column                 | Type          | Constraint                         |
| ---------------------- | ------------- | ---------------------------------- |
| id                     | bigint, auto  | PK                                 |
| sort                   | integer       | default 0, nullable                |
| name                   | string(255)   |                                    |
| color                  | string(255)   | nullable                           |
| is_active              | boolean       | default false                      |
| invoiced_target        | decimal(15,4) | default 0                          |
| company_id             | bigint        | FK → companies, nullable           |
| user_id                | bigint        | FK → users, nullable (team leader) |
| creator_id             | bigint        | FK → users, nullable               |
| deleted_at             | timestamp     | nullable                           |
| created_at, updated_at | timestamp     |                                    |

### 8.2. `sales_team_members`

| Column  | Type   | Constraint                        |
| ------- | ------ | --------------------------------- |
| team_id | bigint | FK → sales_teams, cascadeOnDelete |
| user_id | bigint | FK → users, cascadeOnDelete       |

### 8.3. `sales_order_templates`

| Column                 | Type          | Constraint                    |
| ---------------------- | ------------- | ----------------------------- |
| id                     | bigint, auto  | PK                            |
| sort                   | integer       | default 0, nullable           |
| name                   | string(255)   |                               |
| number_of_days         | integer       | nullable (quotation duration) |
| note                   | text          | nullable                      |
| journal_id             | integer       | nullable                      |
| is_active              | boolean       | default false                 |
| require_signature      | boolean       | default false                 |
| require_payment        | boolean       | default false                 |
| prepayment_percentage  | decimal(15,4) | default 0                     |
| company_id             | bigint        | FK → companies, nullable      |
| creator_id             | bigint        | FK → users, nullable          |
| created_at, updated_at | timestamp     |                               |

### 8.4. `sales_orders`

| Column                 | Type          | Constraint                                    |
| ---------------------- | ------------- | --------------------------------------------- |
| id                     | bigint, auto  | PK                                            |
| name                   | string(255)   | nullable (order reference)                    |
| state                  | string(255)   | nullable (draft,sent,confirmed,done,cancel)   |
| delivery_status        | string(255)   | nullable                                      |
| invoice_status         | string(255)   | nullable                                      |
| date_order             | datetime      | nullable                                      |
| validity_date          | date          | nullable                                      |
| required_date          | date          | nullable                                      |
| note                   | text          | nullable                                      |
| terms_conditions       | text          | nullable                                      |
| amount_tax             | decimal(15,4) | nullable                                      |
| amount_untaxed         | decimal(15,4) | nullable                                      |
| amount_total           | decimal(15,4) | nullable                                      |
| currency_rate          | decimal(15,4) | nullable                                      |
| company_id             | bigint        | FK → companies                                |
| partner_id             | bigint        | FK → partners_partners                        |
| partner_invoice_id     | bigint        | FK → partners_partners                        |
| partner_shipping_id    | bigint        | FK → partners_partners                        |
| user_id                | bigint        | FK → users, nullable (salesperson)            |
| team_id                | bigint        | FK → sales_teams, nullable                    |
| fiscal_position_id     | bigint        | FK → accounts_fiscal_positions, nullable      |
| payment_term_id        | bigint        | FK → accounts_payment_terms, nullable         |
| currency_id            | bigint        | FK → currencies                               |
| journal_id             | bigint        | FK → accounts_journals, nullable              |
| warehouse_id           | bigint        | FK → inventories_warehouses, nullable         |
| procurement_group_id   | bigint        | FK → inventories_procurement_groups, nullable |
| campaign_id            | bigint        | FK → utm_campaigns, nullable                  |
| utm_source_id          | bigint        | FK → utm_sources, nullable                    |
| medium_id              | bigint        | FK → utm_mediums, nullable                    |
| creator_id             | bigint        | FK → users, nullable                          |
| created_at, updated_at | timestamp     |                                               |

### 8.5. `sales_order_lines`

| Column                 | Type          | Constraint                            |
| ---------------------- | ------------- | ------------------------------------- |
| id                     | bigint, auto  | PK                                    |
| name                   | text          | nullable (description)                |
| state                  | string(255)   | nullable                              |
| sequence               | integer       | nullable                              |
| price_unit             | decimal(15,4) | nullable                              |
| price_subtotal         | decimal(15,4) | nullable                              |
| price_total            | decimal(15,4) | nullable                              |
| product_uom_qty        | decimal(15,4) | nullable                              |
| qty_delivered          | decimal(15,4) | nullable                              |
| qty_invoiced           | decimal(15,4) | nullable                              |
| qty_to_invoice         | decimal(15,4) | nullable                              |
| discount               | decimal(15,4) | default 0                             |
| order_id               | bigint        | FK → sales_orders, cascadeOnDelete    |
| product_id             | bigint        | FK → products_products                |
| uom_id                 | bigint        | FK → unit_of_measures                 |
| warehouse_id           | bigint        | FK → inventories_warehouses, nullable |
| route_id               | bigint        | FK → inventories_routes, nullable     |
| creator_id             | bigint        | FK → users, nullable                  |
| created_at, updated_at | timestamp     |                                       |

### 8.6. `sales_order_options`

| Column                 | Type          | Constraint                         |
| ---------------------- | ------------- | ---------------------------------- |
| id                     | bigint, auto  | PK                                 |
| order_id               | bigint        | FK → sales_orders, cascadeOnDelete |
| product_id             | bigint        | FK → products_products             |
| name                   | string(255)   |                                    |
| quantity               | decimal(15,4) |                                    |
| price_unit             | decimal(15,4) |                                    |
| uom_id                 | bigint        | FK → unit_of_measures              |
| created_at, updated_at | timestamp     |                                    |

### 8.7. `sales_order_line_taxes` (pivot)

| Column        | Type   | Constraint                              |
| ------------- | ------ | --------------------------------------- |
| order_line_id | bigint | FK → sales_order_lines, cascadeOnDelete |
| tax_id        | bigint | FK → accounts_taxes, cascadeOnDelete    |

### 8.8. `sales_tags`

| Column                 | Type         | Constraint |
| ---------------------- | ------------ | ---------- |
| id                     | bigint, auto | PK         |
| name                   | string(255)  |            |
| color                  | string(255)  | nullable   |
| created_at, updated_at | timestamp    |            |

### 8.9. `sales_order_tags` (pivot)

| Column   | Type   | Constraint                         |
| -------- | ------ | ---------------------------------- |
| order_id | bigint | FK → sales_orders, cascadeOnDelete |
| tag_id   | bigint | FK → sales_tags, cascadeOnDelete   |

### 8.10. `sales_order_invoices` (pivot)

| Column     | Type   | Constraint                                   |
| ---------- | ------ | -------------------------------------------- |
| order_id   | bigint | FK → sales_orders, cascadeOnDelete           |
| invoice_id | bigint | FK → accounts_account_moves, cascadeOnDelete |

### 8.11. `sales_order_line_invoices` (pivot)

| Column        | Type   | Constraint                                   |
| ------------- | ------ | -------------------------------------------- |
| order_line_id | bigint | FK → sales_order_lines, cascadeOnDelete      |
| move_id       | bigint | FK → accounts_account_moves, cascadeOnDelete |

### 8.12. `sales_order_template_products`

| Column                 | Type          | Constraint                                  |
| ---------------------- | ------------- | ------------------------------------------- |
| id                     | bigint, auto  | PK                                          |
| quantity               | decimal(15,4) | default 1                                   |
| template_id            | bigint        | FK → sales_order_templates, cascadeOnDelete |
| product_id             | bigint        | FK → products_products                      |
| uom_id                 | bigint        | FK → unit_of_measures                       |
| created_at, updated_at | timestamp     |                                             |

### 8.13. `sales_advance_payment_invoices`

| Column                 | Type          | Constraint                       |
| ---------------------- | ------------- | -------------------------------- |
| id                     | bigint, auto  | PK                               |
| amount                 | decimal(15,4) | default 0                        |
| advance_payment_method | string(255)   |                                  |
| fixed_amount           | decimal(15,4) | default 0                        |
| product_id             | bigint        | FK → products_products, nullable |
| count                  | integer       | default 0                        |
| creator_id             | bigint        | FK → users, nullable             |
| created_at, updated_at | timestamp     |                                  |

### 8.14. `sales_advance_payment_invoice_order_sales` (pivot)

| Column                     | Type   | Constraint                                           |
| -------------------------- | ------ | ---------------------------------------------------- |
| advance_payment_invoice_id | bigint | FK → sales_advance_payment_invoices, cascadeOnDelete |
| sale_order_id              | bigint | FK → sales_orders, cascadeOnDelete                   |

---

## 9. PURCHASES PLUGIN (Webkul\Purchase)

### 9.1. `purchases_order_groups`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 9.2. `purchases_requisitions`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| name                   | string(255)  |                                      |
| state                  | string(255)  | (draft,in_progress,open,done,cancel) |
| type                   | string(255)  |                                      |
| date_from              | datetime     | nullable                             |
| date_to                | datetime     | nullable                             |
| user_id                | bigint       | FK → users, nullable                 |
| company_id             | bigint       | FK → companies                       |
| creator_id             | bigint       | FK → users, nullable                 |
| created_at, updated_at | timestamp    |                                      |

### 9.3. `purchases_requisition_lines`

| Column                 | Type          | Constraint                                   |
| ---------------------- | ------------- | -------------------------------------------- |
| id                     | bigint, auto  | PK                                           |
| product_qty            | decimal(15,4) |                                              |
| price_unit             | decimal(15,4) | nullable                                     |
| scheduled_date         | datetime      | nullable                                     |
| requisition_id         | bigint        | FK → purchases_requisitions, cascadeOnDelete |
| product_id             | bigint        | FK → products_products                       |
| uom_id                 | bigint        | FK → unit_of_measures                        |
| creator_id             | bigint        | FK → users, nullable                         |
| created_at, updated_at | timestamp     |                                              |

### 9.4. `purchases_orders`

| Column                 | Type          | Constraint                                    |
| ---------------------- | ------------- | --------------------------------------------- |
| id                     | bigint, auto  | PK                                            |
| name                   | string(255)   | nullable                                      |
| state                  | string(255)   | (draft,sent,confirmed,done,cancel)            |
| date_order             | datetime      | nullable                                      |
| date_approve           | datetime      | nullable                                      |
| date_planned           | datetime      | nullable                                      |
| notes                  | text          | nullable                                      |
| amount_tax             | decimal(15,4) | nullable                                      |
| amount_untaxed         | decimal(15,4) | nullable                                      |
| amount_total           | decimal(15,4) | nullable                                      |
| partner_id             | bigint        | FK → partners_partners                        |
| company_id             | bigint        | FK → companies                                |
| currency_id            | bigint        | FK → currencies                               |
| user_id                | bigint        | FK → users, nullable                          |
| group_id               | bigint        | FK → purchases_order_groups, nullable         |
| requisition_id         | bigint        | FK → purchases_requisitions, nullable         |
| operation_type_id      | bigint        | FK → inventories_operation_types, nullable    |
| destination_address_id | bigint        | FK → partners_partners, nullable              |
| procurement_group_id   | bigint        | FK → inventories_procurement_groups, nullable |
| creator_id             | bigint        | FK → users, nullable                          |
| created_at, updated_at | timestamp     |                                               |

### 9.5. `purchases_order_lines`

| Column                 | Type          | Constraint                                    |
| ---------------------- | ------------- | --------------------------------------------- |
| id                     | bigint, auto  | PK                                            |
| name                   | text          | nullable                                      |
| sequence               | integer       | nullable                                      |
| price_unit             | decimal(15,4) | nullable                                      |
| price_subtotal         | decimal(15,4) | nullable                                      |
| price_total            | decimal(15,4) | nullable                                      |
| product_qty            | decimal(15,4) | nullable                                      |
| qty_received           | decimal(15,4) | nullable                                      |
| qty_invoiced           | decimal(15,4) | nullable                                      |
| qty_to_invoice         | decimal(15,4) | nullable                                      |
| discount               | decimal(15,4) | default 0                                     |
| scheduled_date         | datetime      | nullable                                      |
| order_id               | bigint        | FK → purchases_orders, cascadeOnDelete        |
| product_id             | bigint        | FK → products_products                        |
| uom_id                 | bigint        | FK → unit_of_measures                         |
| partner_id             | bigint        | FK → partners_partners, nullable              |
| currency_id            | bigint        | FK → currencies, nullable                     |
| final_location_id      | bigint        | FK → inventories_locations, nullable          |
| order_point_id         | bigint        | FK → inventories_order_points, nullable       |
| procurement_group_id   | bigint        | FK → inventories_procurement_groups, nullable |
| creator_id             | bigint        | FK → users, nullable                          |
| created_at, updated_at | timestamp     |                                               |

### 9.6. `purchases_order_line_taxes` (pivot)

| Column        | Type   | Constraint                                  |
| ------------- | ------ | ------------------------------------------- |
| order_line_id | bigint | FK → purchases_order_lines, cascadeOnDelete |
| tax_id        | bigint | FK → accounts_taxes, cascadeOnDelete        |

### 9.7. `purchases_order_account_moves` (pivot)

| Column            | Type   | Constraint                                   |
| ----------------- | ------ | -------------------------------------------- |
| purchase_order_id | bigint | FK → purchases_orders, cascadeOnDelete       |
| account_move_id   | bigint | FK → accounts_account_moves, cascadeOnDelete |

### 9.8. `purchases_order_operations` (pivot)

| Column                 | Type   | Constraint                                   |
| ---------------------- | ------ | -------------------------------------------- |
| purchase_order_id      | bigint | FK → purchases_orders, cascadeOnDelete       |
| inventory_operation_id | bigint | FK → inventories_operations, cascadeOnDelete |

### 9.9. `purchases_order_line_moves` (pivot)

| Column                 | Type   | Constraint                                  |
| ---------------------- | ------ | ------------------------------------------- |
| purchase_order_line_id | bigint | FK → purchases_order_lines, cascadeOnDelete |
| inventory_move_id      | bigint | FK → inventories_moves, cascadeOnDelete     |

---

## 10. INVENTORIES PLUGIN (Webkul\Inventory)

### 10.1. `inventories_tags`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| color                  | string(255)  | nullable             |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 10.2. `inventories_warehouses`

| Column                     | Type         | Constraint                                 |
| -------------------------- | ------------ | ------------------------------------------ |
| id                         | bigint, auto | PK                                         |
| name                       | string(255)  |                                            |
| code                       | string(255)  | nullable                                   |
| active                     | boolean      | default true                               |
| company_id                 | bigint       | FK → companies                             |
| partner_id                 | bigint       | FK → partners_partners, nullable           |
| route_id                   | bigint       | FK → inventories_routes, nullable          |
| location_id                | bigint       | FK → inventories_locations, nullable       |
| view_location_id           | bigint       | FK → inventories_locations, nullable       |
| **Manufacturing columns:** |              |                                            |
| manufacture_pull_id        | bigint       | FK → inventories_rules, nullable           |
| manufacture_mto_pull_id    | bigint       | FK → inventories_rules, nullable           |
| pbm_mto_pull_id            | bigint       | FK → inventories_rules, nullable           |
| sam_rule_id                | bigint       | FK → inventories_rules, nullable           |
| manu_type_id               | bigint       | FK → inventories_operation_types, nullable |
| pbm_type_id                | bigint       | FK → inventories_operation_types, nullable |
| sam_type_id                | bigint       | FK → inventories_operation_types, nullable |
| pbm_route_id               | bigint       | FK → inventories_routes, nullable          |
| pbm_loc_id                 | bigint       | FK → inventories_locations, nullable       |
| sam_loc_id                 | bigint       | FK → inventories_locations, nullable       |
| manufacture_steps          | string(255)  | default 'one_step'                         |
| manufacture_to_resupply    | boolean      | nullable                                   |
| creator_id                 | bigint       | FK → users, nullable                       |
| created_at, updated_at     | timestamp    |                                            |

### 10.3. `inventories_storage_categories`

| Column                 | Type          | Constraint                                    |
| ---------------------- | ------------- | --------------------------------------------- |
| id                     | bigint, auto  | PK                                            |
| name                   | string(255)   |                                               |
| parent_id              | bigint        | FK → inventories_storage_categories, nullable |
| allow_new_product      | boolean       | default true                                  |
| allow_mixed_packages   | boolean       | default false                                 |
| max_weight             | decimal(15,4) | nullable                                      |
| creator_id             | bigint        | FK → users, nullable                          |
| created_at, updated_at | timestamp     |                                               |

### 10.4. `inventories_locations`

| Column                 | Type         | Constraint                                                            |
| ---------------------- | ------------ | --------------------------------------------------------------------- |
| id                     | bigint, auto | PK                                                                    |
| name                   | string(255)  |                                                                       |
| full_name              | string(255)  | nullable                                                              |
| code                   | string(255)  | nullable                                                              |
| parent_path            | string(255)  | nullable                                                              |
| location_type          | string(255)  | ('internal','customer','supplier','inventory','transit','production') |
| description            | text         | nullable                                                              |
| active                 | boolean      | default true                                                          |
| parent_id              | bigint       | FK → inventories_locations, nullable                                  |
| company_id             | bigint       | FK → companies, nullable                                              |
| creator_id             | bigint       | FK → users, nullable                                                  |
| created_at, updated_at | timestamp    |                                                                       |

### 10.5. `inventories_operation_types`

| Column                  | Type         | Constraint                                         |
| ----------------------- | ------------ | -------------------------------------------------- |
| id                      | bigint, auto | PK                                                 |
| name                    | string(255)  |                                                    |
| code                    | string(255)  | nullable                                           |
| sequence                | integer      | nullable                                           |
| operation_type          | string(255)  | ('incoming','outgoing','internal','manufacturing') |
| active                  | boolean      | default true                                       |
| use_create_backorder    | boolean      | default true                                       |
| use_existing_lots       | boolean      | default false                                      |
| create_new_lot          | boolean      | default false                                      |
| auto_validate           | boolean      | default false                                      |
| company_id              | bigint       | FK → companies, nullable                           |
| warehouse_id            | bigint       | FK → inventories_warehouses, nullable              |
| source_location_id      | bigint       | FK → inventories_locations, nullable               |
| destination_location_id | bigint       | FK → inventories_locations, nullable               |
| creator_id              | bigint       | FK → users, nullable                               |
| created_at, updated_at  | timestamp    |                                                    |

### 10.6. `inventories_routes`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| active                 | boolean      | default true             |
| sequence               | integer      | nullable                 |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| created_at, updated_at | timestamp    |                          |

### 10.7. `inventories_rules`

| Column                  | Type         | Constraint                                    |
| ----------------------- | ------------ | --------------------------------------------- |
| id                      | bigint, auto | PK                                            |
| name                    | string(255)  |                                               |
| action                  | string(255)  | ('pull','push','pull_push')                   |
| active                  | boolean      | default true                                  |
| sequence                | integer      | nullable                                      |
| delay                   | integer      | default 0                                     |
| procure_method          | string(255)  |                                               |
| warehouse_id            | bigint       | FK → inventories_warehouses, nullable         |
| route_id                | bigint       | FK → inventories_routes, nullable             |
| source_location_id      | bigint       | FK → inventories_locations, nullable          |
| destination_location_id | bigint       | FK → inventories_locations, nullable          |
| operation_type_id       | bigint       | FK → inventories_operation_types, nullable    |
| procurement_group_id    | bigint       | FK → inventories_procurement_groups, nullable |
| company_id              | bigint       | FK → companies, nullable                      |
| creator_id              | bigint       | FK → users, nullable                          |
| created_at, updated_at  | timestamp    |                                               |

### 10.8. `inventories_route_warehouses` (pivot)

| Column       | Type   | Constraint                                   |
| ------------ | ------ | -------------------------------------------- |
| route_id     | bigint | FK → inventories_routes, cascadeOnDelete     |
| warehouse_id | bigint | FK → inventories_warehouses, cascadeOnDelete |

### 10.9. `inventories_warehouse_resupplies` (pivot)

| Column                | Type   | Constraint                                   |
| --------------------- | ------ | -------------------------------------------- |
| warehouse_id          | bigint | FK → inventories_warehouses, cascadeOnDelete |
| resupply_warehouse_id | bigint | FK → inventories_warehouses, cascadeOnDelete |

### 10.10. `inventories_package_types`

| Column                 | Type          | Constraint                |
| ---------------------- | ------------- | ------------------------- |
| id                     | bigint, auto  | PK                        |
| name                   | string(255)   |                           |
| barcode                | string(255)   | nullable                  |
| package_use            | string(255)   | ('reusable','disposable') |
| height                 | decimal(15,4) | nullable                  |
| width                  | decimal(15,4) | nullable                  |
| length                 | decimal(15,4) | nullable                  |
| max_weight             | decimal(15,4) | nullable                  |
| creator_id             | bigint        | FK → users, nullable      |
| deleted_at             | timestamp     | nullable                  |
| created_at, updated_at | timestamp     |                           |

### 10.11. `inventories_packages`

| Column                 | Type         | Constraint                               |
| ---------------------- | ------------ | ---------------------------------------- |
| id                     | bigint, auto | PK                                       |
| name                   | string(255)  |                                          |
| barcode                | string(255)  | nullable                                 |
| active                 | boolean      | default true                             |
| package_type_id        | bigint       | FK → inventories_package_types, nullable |
| location_id            | bigint       | FK → inventories_locations, nullable     |
| company_id             | bigint       | FK → companies, nullable                 |
| creator_id             | bigint       | FK → users, nullable                     |
| created_at, updated_at | timestamp    |                                          |

### 10.12. `inventories_storage_category_capacities`

| Column                                       | Type         | Constraint                                           |
| -------------------------------------------- | ------------ | ---------------------------------------------------- |
| id                                           | bigint, auto | PK                                                   |
| product_id                                   | bigint       | FK → products_products, nullable                     |
| package_type_id                              | bigint       | FK → inventories_package_types, nullable             |
| storage_category_id                          | bigint       | FK → inventories_storage_categories, cascadeOnDelete |
| quantity                                     | integer      | default 1                                            |
| creator_id                                   | bigint       | FK → users, nullable                                 |
| UNIQUE(product_id, storage_category_id)      |              |                                                      |
| UNIQUE(package_type_id, storage_category_id) |              |                                                      |
| created_at, updated_at                       | timestamp    |                                                      |

### 10.13. `inventories_route_packagings` (pivot)

| Column       | Type   | Constraint                                |
| ------------ | ------ | ----------------------------------------- |
| route_id     | bigint | FK → inventories_routes, cascadeOnDelete  |
| packaging_id | bigint | FK → products_packagings, cascadeOnDelete |

### 10.14. `inventories_lots`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| name                   | string(255)  | indexed                              |
| description            | text         | nullable                             |
| reference              | string(255)  | nullable                             |
| properties             | json         | nullable                             |
| expiry_reminded        | boolean      | default false                        |
| expiration_date        | datetime     | nullable                             |
| use_date               | datetime     | nullable                             |
| removal_date           | datetime     | nullable                             |
| alert_date             | datetime     | nullable                             |
| product_id             | bigint       | FK → products_products               |
| uom_id                 | bigint       | FK → unit_of_measures, nullable      |
| location_id            | bigint       | FK → inventories_locations, nullable |
| company_id             | bigint       | FK → companies, nullable             |
| creator_id             | bigint       | FK → users, nullable                 |
| created_at, updated_at | timestamp    |                                      |

### 10.15. `inventories_product_quantities`

| Column                  | Type          | Constraint                                    |
| ----------------------- | ------------- | --------------------------------------------- |
| id                      | bigint, auto  | PK                                            |
| quantity                | decimal(15,4) | default 0                                     |
| reserved_quantity       | decimal(15,4) | default 0                                     |
| counted_quantity        | decimal(15,4) | default 0                                     |
| difference_quantity     | decimal(15,4) | default 0                                     |
| inventory_diff_quantity | decimal(15,4) | default 0                                     |
| inventory_quantity_set  | boolean       | default false                                 |
| scheduled_at            | date          | nullable                                      |
| incoming_at             | datetime      |                                               |
| product_id              | bigint        | FK → products_products                        |
| location_id             | bigint        | FK → inventories_locations                    |
| storage_category_id     | bigint        | FK → inventories_storage_categories, nullable |
| lot_id                  | bigint        | FK → inventories_lots, nullable               |
| package_id              | bigint        | FK → inventories_packages, nullable           |
| partner_id              | bigint        | FK → partners_partners, nullable              |
| user_id                 | bigint        | FK → users, nullable                          |
| company_id              | bigint        | FK → companies, nullable                      |
| creator_id              | bigint        | FK → users, nullable                          |
| created_at, updated_at  | timestamp     |                                               |

### 10.16. `inventories_product_quantity_relocations`

| Column                  | Type         | Constraint                           |
| ----------------------- | ------------ | ------------------------------------ |
| id                      | bigint, auto | PK                                   |
| description             | text         | nullable                             |
| destination_location_id | bigint       | FK → inventories_locations, nullable |
| destination_package_id  | bigint       | FK → inventories_packages            |
| creator_id              | bigint       | FK → users, nullable                 |
| created_at, updated_at  | timestamp    |                                      |

### 10.17. `inventories_operations`

| Column                  | Type         | Constraint                                      |
| ----------------------- | ------------ | ----------------------------------------------- |
| id                      | bigint, auto | PK                                              |
| name                    | string(255)  | nullable                                        |
| description             | text         | nullable                                        |
| origin                  | string(255)  | nullable                                        |
| move_type               | string(255)  | default 'direct'                                |
| state                   | string(255)  | nullable (draft,confirmed,assigned,done,cancel) |
| is_favorite             | boolean      | default false                                   |
| has_deadline_issue      | boolean      | default false                                   |
| is_printed              | boolean      | default false                                   |
| is_locked               | boolean      | default false                                   |
| deadline                | datetime     | nullable                                        |
| scheduled_at            | datetime     | nullable                                        |
| closed_at               | datetime     | nullable                                        |
| user_id                 | bigint       | FK → users, nullable                            |
| owner_id                | bigint       | FK → users, nullable                            |
| operation_type_id       | bigint       | FK → inventories_operation_types                |
| source_location_id      | bigint       | FK → inventories_locations                      |
| destination_location_id | bigint       | FK → inventories_locations                      |
| back_order_id           | bigint       | FK → inventories_operations, nullable           |
| return_id               | bigint       | FK → inventories_operations, nullable           |
| partner_id              | bigint       | FK → partners_partners, nullable                |
| sale_order_id           | bigint       | FK → sales_orders, nullable                     |
| procurement_group_id    | bigint       | FK → inventories_procurement_groups, nullable   |
| company_id              | bigint       | FK → companies, nullable                        |
| creator_id              | bigint       | FK → users, nullable                            |
| created_at, updated_at  | timestamp    |                                                 |

### 10.18. `inventories_package_levels`

| Column                  | Type         | Constraint                            |
| ----------------------- | ------------ | ------------------------------------- |
| id                      | bigint, auto | PK                                    |
| package_id              | bigint       | FK → inventories_packages             |
| operation_id            | bigint       | FK → inventories_operations, nullable |
| destination_location_id | bigint       | FK → inventories_locations, nullable  |
| company_id              | bigint       | FK → companies                        |
| creator_id              | bigint       | FK → users, nullable                  |
| created_at, updated_at  | timestamp    |                                       |

### 10.19. `inventories_package_destinations`

| Column                  | Type         | Constraint                            |
| ----------------------- | ------------ | ------------------------------------- |
| id                      | bigint, auto | PK                                    |
| operation_id            | bigint       | FK → inventories_operations, nullable |
| destination_location_id | bigint       | FK → inventories_locations, nullable  |
| creator_id              | bigint       | FK → users, nullable                  |
| created_at, updated_at  | timestamp    |                                       |

### 10.20. `inventories_scraps`

| Column                  | Type          | Constraint                            |
| ----------------------- | ------------- | ------------------------------------- |
| id                      | bigint, auto  | PK                                    |
| name                    | string(255)   |                                       |
| origin                  | string(255)   | nullable                              |
| state                   | string(255)   | nullable                              |
| qty                     | decimal(15,4) | default 0                             |
| should_replenish        | boolean       | default false                         |
| closed_at               | date          | nullable                              |
| product_id              | bigint        | FK → products_products                |
| uom_id                  | bigint        | FK → unit_of_measures                 |
| lot_id                  | bigint        | FK → inventories_lots, nullable       |
| package_id              | bigint        | FK → inventories_packages, nullable   |
| partner_id              | bigint        | FK → partners_partners, nullable      |
| operation_id            | bigint        | FK → inventories_operations, nullable |
| source_location_id      | bigint        | FK → inventories_locations            |
| destination_location_id | bigint        | FK → inventories_locations            |
| company_id              | bigint        | FK → companies                        |
| creator_id              | bigint        | FK → users, nullable                  |
| created_at, updated_at  | timestamp     |                                       |

### 10.21. `inventories_scrap_tags` (pivot)

| Column   | Type   | Constraint                               |
| -------- | ------ | ---------------------------------------- |
| tag_id   | bigint | FK → inventories_tags, cascadeOnDelete   |
| scrap_id | bigint | FK → inventories_scraps, cascadeOnDelete |

### 10.22. `inventories_moves`

| Column                   | Type          | Constraint                                               |
| ------------------------ | ------------- | -------------------------------------------------------- |
| id                       | bigint, auto  | PK                                                       |
| name                     | string(255)   |                                                          |
| state                    | string(255)   | nullable (draft,confirmed,assigned,done,cancel)          |
| origin                   | string(255)   | nullable                                                 |
| procure_method           | string(255)   | default 'make_to_stock'                                  |
| reference                | string(255)   | nullable                                                 |
| description_picking      | text          | nullable                                                 |
| next_serial              | string(255)   | nullable                                                 |
| next_serial_count        | integer       | nullable                                                 |
| is_favorite              | boolean       | default false                                            |
| is_refund                | boolean       | default false                                            |
| product_qty              | decimal(15,4) | default 0                                                |
| product_uom_qty          | decimal(15,4) | default 0                                                |
| quantity                 | decimal(15,4) | default 0                                                |
| price_unit               | double        | nullable, default 0                                      |
| cost_share               | decimal(15,4) | nullable                                                 |
| is_picked                | boolean       | default false                                            |
| is_scraped               | boolean       | default false                                            |
| is_inventory             | boolean       | default false                                            |
| manual_consumption       | boolean       | nullable                                                 |
| reservation_date         | date          | nullable                                                 |
| scheduled_at             | datetime      |                                                          |
| deadline                 | datetime      | nullable                                                 |
| alert_date               | datetime      | nullable                                                 |
| operation_id             | bigint        | FK → inventories_operations, nullable                    |
| product_id               | bigint        | FK → products_products                                   |
| uom_id                   | bigint        | FK → unit_of_measures                                    |
| source_location_id       | bigint        | FK → inventories_locations                               |
| destination_location_id  | bigint        | FK → inventories_locations                               |
| final_location_id        | bigint        | FK → inventories_locations, nullable                     |
| partner_id               | bigint        | FK → partners_partners, nullable                         |
| scrap_id                 | bigint        | FK → inventories_scraps, nullable                        |
| rule_id                  | bigint        | FK → inventories_rules, nullable                         |
| operation_type_id        | bigint        | FK → inventories_operation_types, nullable               |
| origin_returned_move_id  | bigint        | FK → inventories_moves, nullable                         |
| restrict_partner_id      | bigint        | FK → partners_partners, nullable                         |
| warehouse_id             | bigint        | FK → inventories_warehouses, nullable                    |
| package_level_id         | bigint        | FK → inventories_package_levels, nullable                |
| product_packaging_id     | bigint        | FK → products_packagings, nullable                       |
| **Cross-plugin FKs:**    |               |                                                          |
| purchase_order_line_id   | bigint        | FK → purchases_order_lines, nullable                     |
| sale_order_line_id       | bigint        | FK → sales_order_lines, nullable                         |
| procurement_group_id     | bigint        | FK → inventories_procurement_groups, nullable            |
| **Manufacturing FKs:**   |               |                                                          |
| created_order_id         | bigint        | FK → manufacturing_orders, nullable                      |
| order_id                 | bigint        | FK → manufacturing_orders, nullable                      |
| raw_material_order_id    | bigint        | FK → manufacturing_orders, nullable                      |
| unbuild_order_id         | bigint        | FK → manufacturing_unbuild_orders, nullable              |
| consume_unbuild_order_id | bigint        | FK → manufacturing_unbuild_orders, nullable              |
| mo_operation_id          | bigint        | FK → manufacturing_operations, nullable                  |
| work_order_id            | bigint        | FK → manufacturing_work_orders, nullable                 |
| bom_line_id              | bigint        | FK → manufacturing_bill_of_material_lines, nullable      |
| byproduct_id             | bigint        | FK → manufacturing_bill_of_material_byproducts, nullable |
| order_finished_lot_id    | bigint        | FK → inventories_lots, nullable                          |
| company_id               | bigint        | FK → companies                                           |
| creator_id               | bigint        | FK → users, nullable                                     |
| created_at, updated_at   | timestamp     |                                                          |

### 10.23. `inventories_move_destinations` (pivot)

| Column              | Type   | Constraint                              |
| ------------------- | ------ | --------------------------------------- |
| origin_move_id      | bigint | FK → inventories_moves, cascadeOnDelete |
| destination_move_id | bigint | FK → inventories_moves, cascadeOnDelete |

### 10.24. `inventories_move_lines`

| Column                  | Type          | Constraint                                |
| ----------------------- | ------------- | ----------------------------------------- |
| id                      | bigint, auto  | PK                                        |
| lot_name                | string(255)   | nullable                                  |
| state                   | string(255)   | nullable                                  |
| reference               | string(255)   | nullable                                  |
| picking_description     | string(255)   | nullable                                  |
| qty                     | decimal(15,4) | default 0                                 |
| uom_qty                 | decimal(15,4) | default 0                                 |
| is_picked               | boolean       | default false                             |
| scheduled_at            | datetime      |                                           |
| move_id                 | bigint        | FK → inventories_moves, nullable          |
| operation_id            | bigint        | FK → inventories_operations, nullable     |
| product_id              | bigint        | FK → products_products, cascadeOnDelete   |
| uom_id                  | bigint        | FK → unit_of_measures                     |
| package_id              | bigint        | FK → inventories_packages, nullable       |
| result_package_id       | bigint        | FK → inventories_packages, nullable       |
| package_level_id        | bigint        | FK → inventories_package_levels, nullable |
| lot_id                  | bigint        | FK → inventories_lots, nullable           |
| partner_id              | bigint        | FK → partners_partners, nullable          |
| source_location_id      | bigint        | FK → inventories_locations                |
| destination_location_id | bigint        | FK → inventories_locations                |
| **Manufacturing FKs:**  |               |                                           |
| work_order_id           | bigint        | FK → manufacturing_work_orders, nullable  |
| order_id                | bigint        | FK → manufacturing_orders, nullable       |
| company_id              | bigint        | FK → companies                            |
| creator_id              | bigint        | FK → users, nullable                      |
| created_at, updated_at  | timestamp     |                                           |

### 10.25. `inventories_order_points`

| Column                 | Type          | Constraint                                   |
| ---------------------- | ------------- | -------------------------------------------- |
| id                     | bigint, auto  | PK                                           |
| name                   | string(255)   |                                              |
| trigger                | string(255)   | ('manual','auto','min_max')                  |
| snoozed_until          | date          | nullable                                     |
| product_min_qty        | decimal(15,4) | default 0                                    |
| product_max_qty        | decimal(15,4) | default 0                                    |
| qty_multiple           | decimal(15,4) | default 0                                    |
| qty_to_order_manual    | decimal(15,4) | default 0                                    |
| product_id             | bigint        | FK → products_products, cascadeOnDelete      |
| product_category_id    | bigint        | FK → products_categories, nullable           |
| warehouse_id           | bigint        | FK → inventories_warehouses, cascadeOnDelete |
| location_id            | bigint        | FK → inventories_locations, cascadeOnDelete  |
| route_id               | bigint        | FK → inventories_routes, nullable            |
| company_id             | bigint        | FK → companies                               |
| creator_id             | bigint        | FK → users, nullable                         |
| deleted_at             | timestamp     | nullable                                     |
| created_at, updated_at | timestamp     |                                              |

### 10.26. `inventories_procurement_groups`

| Column                 | Type         | Constraint                       |
| ---------------------- | ------------ | -------------------------------- |
| id                     | bigint, auto | PK                               |
| name                   | string(255)  | nullable                         |
| move_type              | string(255)  | nullable                         |
| partner_id             | bigint       | FK → partners_partners, nullable |
| sale_order_id          | bigint       | FK → sales_orders, nullable      |
| creator_id             | bigint       | FK → users, nullable             |
| created_at, updated_at | timestamp    |                                  |

### 10.27. `inventories_route_moves` (pivot)

| Column   | Type   | Constraint                               |
| -------- | ------ | ---------------------------------------- |
| route_id | bigint | FK → inventories_routes, cascadeOnDelete |
| move_id  | bigint | FK → inventories_moves, cascadeOnDelete  |

### 10.28. `inventories_category_routes` (pivot)

| Column      | Type   | Constraint                                |
| ----------- | ------ | ----------------------------------------- |
| category_id | bigint | FK → products_categories, cascadeOnDelete |
| route_id    | bigint | FK → inventories_routes, cascadeOnDelete  |

### 10.29. `inventories_product_routes` (pivot)

| Column     | Type   | Constraint                               |
| ---------- | ------ | ---------------------------------------- |
| product_id | bigint | FK → products_products, cascadeOnDelete  |
| route_id   | bigint | FK → inventories_routes, cascadeOnDelete |

---

## 11. EMPLOYEES PLUGIN (Webkul\Employee)

### 11.1. `employees_work_locations`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| active                 | boolean      | default true             |
| location_type          | string(255)  | nullable                 |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| created_at, updated_at | timestamp    |                          |

### 11.2. `employees_departments`

| Column                 | Type         | Constraint                           |
| ---------------------- | ------------ | ------------------------------------ |
| id                     | bigint, auto | PK                                   |
| name                   | string(255)  |                                      |
| manager_id             | bigint       | FK → employees_employees, nullable   |
| company_id             | bigint       | FK → companies, nullable             |
| parent_id              | bigint       | FK → employees_departments, nullable |
| creator_id             | bigint       | FK → users, nullable                 |
| created_at, updated_at | timestamp    |                                      |

### 11.3. `employees_categories`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| created_at, updated_at | timestamp    |                          |

### 11.4. `employees_employment_types`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 11.5. `employees_skill_types`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 11.6. `employees_skill_levels`

| Column                 | Type         | Constraint                                  |
| ---------------------- | ------------ | ------------------------------------------- |
| id                     | bigint, auto | PK                                          |
| name                   | string(255)  |                                             |
| level_progress         | integer      | nullable                                    |
| skill_type_id          | bigint       | FK → employees_skill_types, cascadeOnDelete |
| creator_id             | bigint       | FK → users, nullable                        |
| created_at, updated_at | timestamp    |                                             |

### 11.7. `employees_skills`

| Column                 | Type         | Constraint                                  |
| ---------------------- | ------------ | ------------------------------------------- |
| id                     | bigint, auto | PK                                          |
| name                   | string(255)  |                                             |
| skill_type_id          | bigint       | FK → employees_skill_types, cascadeOnDelete |
| creator_id             | bigint       | FK → users, nullable                        |
| created_at, updated_at | timestamp    |                                             |

### 11.8. `employees_job_positions`

| Column                   | Type         | Constraint                           |
| ------------------------ | ------------ | ------------------------------------ |
| id                       | bigint, auto | PK                                   |
| name                     | string(255)  |                                      |
| description              | text         | nullable                             |
| expected_employees       | integer      | nullable                             |
| is_active                | boolean      | default true                         |
| department_id            | bigint       | FK → employees_departments, nullable |
| company_id               | bigint       | FK → companies, nullable             |
| creator_id               | bigint       | FK → users, nullable                 |
| **Recruitment columns:** |              |                                      |
| recruiter_id             | bigint       | FK → users, nullable                 |
| interviewer_id           | bigint       | FK → users, nullable                 |
| no_of_hired_employee     | integer      | default 0                            |
| no_of_recruitment        | integer      | default 0                            |
| no_of_employee           | integer      | default 0                            |
| created_at, updated_at   | timestamp    |                                      |

### 11.9. `employees_employees`

| Column                 | Type         | Constraint                                |
| ---------------------- | ------------ | ----------------------------------------- |
| id                     | bigint, auto | PK                                        |
| name                   | string(255)  |                                           |
| employee_number        | string(255)  | nullable                                  |
| work_email             | string(255)  | nullable                                  |
| work_phone             | string(255)  | nullable                                  |
| mobile_phone           | string(255)  | nullable                                  |
| gender                 | string(255)  | nullable                                  |
| marital_status         | string(255)  | nullable                                  |
| birth_date             | date         | nullable                                  |
| identification_id      | string(255)  | nullable                                  |
| passport_id            | string(255)  | nullable                                  |
| visa_no                | string(255)  | nullable                                  |
| certificate            | string(255)  | nullable                                  |
| country_of_birth       | string(255)  | nullable                                  |
| place_of_birth         | string(255)  | nullable                                  |
| active                 | boolean      | default true                              |
| notes                  | text         | nullable                                  |
| user_id                | bigint       | FK → users, UNIQUE, nullable              |
| company_id             | bigint       | FK → companies, nullable                  |
| department_id          | bigint       | FK → employees_departments, nullable      |
| job_position_id        | bigint       | FK → employees_job_positions, nullable    |
| work_location_id       | bigint       | FK → employees_work_locations, nullable   |
| calendar_id            | bigint       | FK → calendars, nullable                  |
| employment_type_id     | bigint       | FK → employees_employment_types, nullable |
| manager_id             | bigint       | FK → employees_employees, nullable        |
| coach_id               | bigint       | FK → employees_employees, nullable        |
| creator_id             | bigint       | FK → users, nullable                      |
| created_at, updated_at | timestamp    |                                           |

### 11.10. `employees_employee_skills`

| Column                 | Type         | Constraint                                |
| ---------------------- | ------------ | ----------------------------------------- |
| id                     | bigint, auto | PK                                        |
| level                  | integer      | default 0                                 |
| employee_id            | bigint       | FK → employees_employees, cascadeOnDelete |
| skill_id               | bigint       | FK → employees_skills, cascadeOnDelete    |
| skill_level_id         | bigint       | FK → employees_skill_levels, nullable     |
| creator_id             | bigint       | FK → users, nullable                      |
| created_at, updated_at | timestamp    |                                           |

### 11.11. `employees_employee_categories` (pivot)

| Column      | Type   | Constraint                                 |
| ----------- | ------ | ------------------------------------------ |
| employee_id | bigint | FK → employees_employees, cascadeOnDelete  |
| category_id | bigint | FK → employees_categories, cascadeOnDelete |

### 11.12. `employees_employee_resume_line_types`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 11.13. `employees_employee_resumes`

| Column                 | Type         | Constraint                                          |
| ---------------------- | ------------ | --------------------------------------------------- |
| id                     | bigint, auto | PK                                                  |
| name                   | string(255)  |                                                     |
| date_start             | date         | nullable                                            |
| date_end               | date         | nullable                                            |
| description            | text         | nullable                                            |
| is_active              | boolean      | default true                                        |
| employee_id            | bigint       | FK → employees_employees, cascadeOnDelete           |
| line_type_id           | bigint       | FK → employees_employee_resume_line_types, nullable |
| creator_id             | bigint       | FK → users, nullable                                |
| created_at, updated_at | timestamp    |                                                     |

### 11.14. `employees_departure_reasons`

| Column                 | Type         | Constraint                                         |
| ---------------------- | ------------ | -------------------------------------------------- |
| id                     | bigint, auto | PK                                                 |
| name                   | string(255)  |                                                    |
| reason_type            | string(255)  | ('resignation','retirement','termination','other') |
| creator_id             | bigint       | FK → users, nullable                               |
| created_at, updated_at | timestamp    |                                                    |

### 11.15. `job_position_skills` (pivot)

| Column          | Type   | Constraint                                    |
| --------------- | ------ | --------------------------------------------- |
| job_position_id | bigint | FK → employees_job_positions, cascadeOnDelete |
| skill_id        | bigint | FK → employees_skills, cascadeOnDelete        |

---

## 12. ACCOUNTING PLUGIN (Webkul\Account / Webkul\Accounting)

### 12.1. `accounts_accounts`

| Column                 | Type         | Constraint                                                                                     |
| ---------------------- | ------------ | ---------------------------------------------------------------------------------------------- |
| id                     | bigint, auto | PK                                                                                             |
| name                   | string(255)  |                                                                                                |
| code                   | string(255)  | nullable                                                                                       |
| type                   | string(255)  | ('receivable','payable','liquidity','current_asset','fixed_asset','equity','income','expense') |
| active                 | boolean      | default true                                                                                   |
| reconcile              | boolean      | default false                                                                                  |
| note                   | text         | nullable                                                                                       |
| currency_id            | bigint       | FK → currencies, nullable                                                                      |
| company_id             | bigint       | FK → companies, nullable                                                                       |
| parent_id              | bigint       | FK → accounts_accounts, nullable                                                               |
| creator_id             | bigint       | FK → users, nullable                                                                           |
| created_at, updated_at | timestamp    |                                                                                                |

### 12.2. `accounts_account_companies` (pivot)

| Column                         | Type         | Constraint                              |
| ------------------------------ | ------------ | --------------------------------------- |
| id                             | bigint, auto | PK                                      |
| account_id                     | bigint       | FK → accounts_accounts, cascadeOnDelete |
| company_id                     | bigint       | FK → companies, cascadeOnDelete         |
| UNIQUE(account_id, company_id) |              |                                         |
| created_at, updated_at         | timestamp    |                                         |

### 12.3. `accounts_account_tags`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| color                  | string(255)  | nullable             |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 12.4. `accounts_taxes`

| Column                 | Type          | Constraint                         |
| ---------------------- | ------------- | ---------------------------------- |
| id                     | bigint, auto  | PK                                 |
| name                   | string(255)   |                                    |
| type                   | string(255)   | ('percent','fixed','group')        |
| amount                 | decimal(15,4) | default 0                          |
| description            | text          | nullable                           |
| price_include          | boolean       | default false                      |
| include_base_amount    | boolean       | default false                      |
| is_active              | boolean       | default true                       |
| analytic               | boolean       | default false                      |
| tax_group_id           | bigint        | FK → accounts_tax_groups, nullable |
| company_id             | bigint        | FK → companies, nullable           |
| cash_basis_account_id  | bigint        | FK → accounts_accounts, nullable   |
| creator_id             | bigint        | FK → users, nullable               |
| created_at, updated_at | timestamp     |                                    |

### 12.5. `accounts_tax_partition_lines`

| Column                 | Type          | Constraint                           |
| ---------------------- | ------------- | ------------------------------------ |
| id                     | bigint, auto  | PK                                   |
| factor_percent         | decimal(15,4) | default 0                            |
| tax_id                 | bigint        | FK → accounts_taxes, cascadeOnDelete |
| account_id             | bigint        | FK → accounts_accounts, nullable     |
| company_id             | bigint        | FK → companies, nullable             |
| creator_id             | bigint        | FK → users, nullable                 |
| created_at, updated_at | timestamp     |                                      |

### 12.6. `accounts_tax_taxes` (pivot)

| Column       | Type   | Constraint                           |
| ------------ | ------ | ------------------------------------ |
| tax_id       | bigint | FK → accounts_taxes, cascadeOnDelete |
| child_tax_id | bigint | FK → accounts_taxes, cascadeOnDelete |

### 12.7. `accounts_account_taxes` (pivot)

| Column     | Type   | Constraint                              |
| ---------- | ------ | --------------------------------------- |
| account_id | bigint | FK → accounts_accounts, cascadeOnDelete |
| tax_id     | bigint | FK → accounts_taxes, cascadeOnDelete    |

### 12.8. `accounts_fiscal_positions`

| Column                 | Type         | Constraint               |
| ---------------------- | ------------ | ------------------------ |
| id                     | bigint, auto | PK                       |
| name                   | string(255)  |                          |
| active                 | boolean      | default true             |
| company_id             | bigint       | FK → companies, nullable |
| creator_id             | bigint       | FK → users, nullable     |
| created_at, updated_at | timestamp    |                          |

### 12.9. `accounts_fiscal_position_taxes`

| Column                 | Type         | Constraint                                      |
| ---------------------- | ------------ | ----------------------------------------------- |
| id                     | bigint, auto | PK                                              |
| fiscal_position_id     | bigint       | FK → accounts_fiscal_positions, cascadeOnDelete |
| tax_src_id             | bigint       | FK → accounts_taxes                             |
| tax_dest_id            | bigint       | FK → accounts_taxes                             |
| creator_id             | bigint       | FK → users, nullable                            |
| created_at, updated_at | timestamp    |                                                 |

### 12.10. `accounts_fiscal_position_accounts`

| Column                 | Type         | Constraint                                      |
| ---------------------- | ------------ | ----------------------------------------------- |
| id                     | bigint, auto | PK                                              |
| fiscal_position_id     | bigint       | FK → accounts_fiscal_positions, cascadeOnDelete |
| account_src_id         | bigint       | FK → accounts_accounts                          |
| account_dest_id        | bigint       | FK → accounts_accounts                          |
| creator_id             | bigint       | FK → users, nullable                            |
| created_at, updated_at | timestamp    |                                                 |

### 12.11. `accounts_cash_roundings`

| Column                 | Type          | Constraint                     |
| ---------------------- | ------------- | ------------------------------ |
| id                     | bigint, auto  | PK                             |
| name                   | string(255)   |                                |
| rounding               | decimal(15,4) | default 0                      |
| strategy               | string(255)   | ('bigger','smaller','nearest') |
| company_id             | bigint        | FK → companies, nullable       |
| creator_id             | bigint        | FK → users, nullable           |
| created_at, updated_at | timestamp     |                                |

### 12.12. `accounts_journals`

| Column                 | Type         | Constraint                                              |
| ---------------------- | ------------ | ------------------------------------------------------- |
| id                     | bigint, auto | PK                                                      |
| name                   | string(255)  |                                                         |
| type                   | string(255)  | ('sale','purchase','cash','bank','general','situation') |
| code                   | string(255)  | nullable                                                |
| active                 | boolean      | default true                                            |
| currency_id            | bigint       | FK → currencies, nullable                               |
| company_id             | bigint       | FK → companies, nullable                                |
| bank_account_id        | bigint       | FK → partners_bank_accounts, nullable                   |
| creator_id             | bigint       | FK → users, nullable                                    |
| created_at, updated_at | timestamp    |                                                         |

### 12.13. `accounts_journal_accounts` (pivot)

| Column     | Type   | Constraint                              |
| ---------- | ------ | --------------------------------------- |
| journal_id | bigint | FK → accounts_journals, cascadeOnDelete |
| account_id | bigint | FK → accounts_accounts, cascadeOnDelete |

### 12.14. `accounts_payment_terms`

| Column                 | Type         | Constraint                       |
| ---------------------- | ------------ | -------------------------------- |
| id                     | bigint, auto | PK                               |
| name                   | string(255)  |                                  |
| type                   | string(255)  | ('fixed','percent','days_after') |
| active                 | boolean      | default true                     |
| note                   | text         | nullable                         |
| company_id             | bigint       | FK → companies, nullable         |
| creator_id             | bigint       | FK → users, nullable             |
| created_at, updated_at | timestamp    |                                  |

### 12.15. `accounts_payment_terms_lines`

| Column                 | Type          | Constraint                                   |
| ---------------------- | ------------- | -------------------------------------------- |
| id                     | bigint, auto  | PK                                           |
| value                  | decimal(15,4) | default 0                                    |
| delay                  | integer       | default 0                                    |
| payment_term_id        | bigint        | FK → accounts_payment_terms, cascadeOnDelete |
| creator_id             | bigint        | FK → users, nullable                         |
| created_at, updated_at | timestamp     |                                              |

### 12.16. `accounts_reconciles`

| Column                 | Type         | Constraint           |
| ---------------------- | ------------ | -------------------- |
| id                     | bigint, auto | PK                   |
| name                   | string(255)  |                      |
| creator_id             | bigint       | FK → users, nullable |
| created_at, updated_at | timestamp    |                      |

### 12.17. `accounts_payment_methods`

| Column                 | Type         | Constraint             |
| ---------------------- | ------------ | ---------------------- |
| id                     | bigint, auto | PK                     |
| name                   | string(255)  |                        |
| code                   | string(255)  | nullable               |
| payment_type           | string(255)  | ('inbound','outbound') |
| creator_id             | bigint       | FK → users, nullable   |
| created_at, updated_at | timestamp    |                        |

### 12.18. `accounts_payment_method_lines`

| Column                 | Type         | Constraint                              |
| ---------------------- | ------------ | --------------------------------------- |
| id                     | bigint, auto | PK                                      |
| name                   | string(255)  |                                         |
| sequence               | integer      | nullable                                |
| payment_method_id      | bigint       | FK → accounts_payment_methods, nullable |
| journal_id             | bigint       | FK → accounts_journals, nullable        |
| company_id             | bigint       | FK → companies, nullable                |
| creator_id             | bigint       | FK → users, nullable                    |
| created_at, updated_at | timestamp    |                                         |

### 12.19. `accounts_bank_statements`

| Column                 | Type          | Constraint                       |
| ---------------------- | ------------- | -------------------------------- |
| id                     | bigint, auto  | PK                               |
| name                   | string(255)   |                                  |
| reference              | string(255)   | nullable                         |
| date                   | date          | nullable                         |
| balance_start          | decimal(15,4) | default 0                        |
| balance_end            | decimal(15,4) | default 0                        |
| journal_id             | bigint        | FK → accounts_journals, nullable |
| company_id             | bigint        | FK → companies, nullable         |
| creator_id             | bigint        | FK → users, nullable             |
| created_at, updated_at | timestamp     |                                  |

### 12.20. `accounts_bank_statement_lines`

| Column                 | Type          | Constraint                                     |
| ---------------------- | ------------- | ---------------------------------------------- |
| id                     | bigint, auto  | PK                                             |
| name                   | string(255)   | nullable                                       |
| date                   | date          | nullable                                       |
| amount                 | decimal(15,4) | default 0                                      |
| reference              | string(255)   | nullable                                       |
| note                   | text          | nullable                                       |
| statement_id           | bigint        | FK → accounts_bank_statements, cascadeOnDelete |
| partner_id             | bigint        | FK → partners_partners, nullable               |
| move_id                | bigint        | FK → accounts_account_moves, nullable          |
| creator_id             | bigint        | FK → users, nullable                           |
| created_at, updated_at | timestamp     |                                                |

### 12.21. `accounts_account_payments`

| Column                 | Type          | Constraint                                   |
| ---------------------- | ------------- | -------------------------------------------- |
| id                     | bigint, auto  | PK                                           |
| name                   | string(255)   |                                              |
| amount                 | decimal(15,4) |                                              |
| payment_type           | string(255)   | ('inbound','outbound')                       |
| state                  | string(255)   | (draft,posted,done,cancel)                   |
| payment_date           | date          | nullable                                     |
| communication          | string(255)   | nullable                                     |
| partner_id             | bigint        | FK → partners_partners                       |
| journal_id             | bigint        | FK → accounts_journals                       |
| currency_id            | bigint        | FK → currencies                              |
| payment_method_line_id | bigint        | FK → accounts_payment_method_lines, nullable |
| move_id                | bigint        | FK → accounts_account_moves, nullable        |
| payment_token_id       | bigint        | FK → payments_payment_tokens, nullable       |
| payment_transaction_id | bigint        | FK → payments_payment_transactions, nullable |
| company_id             | bigint        | FK → companies, nullable                     |
| creator_id             | bigint        | FK → users, nullable                         |
| created_at, updated_at | timestamp     |                                              |

### 12.22. `accounts_account_moves`

| Column                        | Type          | Constraint                                                    |
| ----------------------------- | ------------- | ------------------------------------------------------------- |
| id                            | bigint, auto  | PK                                                            |
| name                          | string(255)   |                                                               |
| reference                     | string(255)   | nullable                                                      |
| state                         | string(255)   | default 'draft'                                               |
| move_type                     | string(255)   | ('entry','out_invoice','out_refund','in_invoice','in_refund') |
| payment_state                 | string(255)   | nullable                                                      |
| invoice_date                  | date          | nullable                                                      |
| accounting_date               | date          | nullable                                                      |
| due_date                      | date          | nullable                                                      |
| narration                     | text          | nullable                                                      |
| amount_total                  | decimal(15,4) | nullable                                                      |
| amount_untaxed                | decimal(15,4) | nullable                                                      |
| amount_tax                    | decimal(15,4) | nullable                                                      |
| amount_residual               | decimal(15,4) | nullable                                                      |
| currency_rate                 | decimal(15,4) | nullable                                                      |
| is_storno                     | boolean       | default false                                                 |
| always_tax_exigible           | boolean       | default false                                                 |
| checked                       | boolean       | default false                                                 |
| posted_before                 | boolean       | default false                                                 |
| made_sequence_gap             | boolean       | default false                                                 |
| is_manually_modified          | boolean       | default false                                                 |
| is_move_sent                  | boolean       | default false                                                 |
| journal_id                    | bigint        | FK → accounts_journals                                        |
| company_id                    | bigint        | FK → companies                                                |
| partner_id                    | bigint        | FK → partners_partners, nullable                              |
| currency_id                   | bigint        | FK → currencies                                               |
| invoice_user_id               | bigint        | FK → users, nullable                                          |
| fiscal_position_id            | bigint        | FK → accounts_fiscal_positions, nullable                      |
| payment_term_id               | bigint        | FK → accounts_payment_terms, nullable                         |
| tax_cash_basis_origin_move_id | bigint        | FK → accounts_account_moves, nullable                         |
| tax_cash_basis_reconcile_id   | bigint        | FK → accounts_partial_reconciles, nullable                    |
| creator_id                    | bigint        | FK → users, nullable                                          |
| created_at, updated_at        | timestamp     |                                                               |

### 12.23. `accounts_account_move_lines`

| Column                   | Type          | Constraint                                   |
| ------------------------ | ------------- | -------------------------------------------- |
| id                       | bigint, auto  | PK                                           |
| name                     | text          | nullable                                     |
| sequence                 | integer       | nullable                                     |
| display_type             | string(255)   | default 'product', nullable                  |
| date                     | date          | nullable                                     |
| date_maturity            | date          | nullable                                     |
| is_imported              | boolean       | default false                                |
| tax_tag_invert           | boolean       | default false                                |
| reconciled               | boolean       | default false                                |
| is_downpayment           | boolean       | default false                                |
| analytic_distribution    | jsonb         | nullable                                     |
| debit                    | decimal(15,4) | nullable                                     |
| credit                   | decimal(15,4) | nullable                                     |
| balance                  | decimal(15,4) | nullable                                     |
| amount_currency          | decimal(15,4) | nullable                                     |
| tax_base_amount          | decimal(15,4) | nullable                                     |
| amount_residual          | decimal(15,4) | nullable                                     |
| amount_residual_currency | decimal(15,4) | nullable                                     |
| quantity                 | decimal(15,4) | nullable                                     |
| price_unit               | decimal(15,4) | nullable                                     |
| price_subtotal           | decimal(15,4) | nullable                                     |
| price_total              | decimal(15,4) | nullable                                     |
| discount                 | decimal(5,2)  | nullable                                     |
| discount_amount_currency | decimal(15,4) | nullable                                     |
| discount_balance         | decimal(15,4) | nullable                                     |
| move_id                  | bigint        | FK → accounts_account_moves, cascadeOnDelete |
| account_id               | bigint        | FK → accounts_accounts                       |
| partner_id               | bigint        | FK → partners_partners, nullable             |
| product_id               | bigint        | FK → products_products, nullable             |
| uom_id                   | bigint        | FK → unit_of_measures, nullable              |
| currency_id              | bigint        | FK → currencies, nullable                    |
| tax_line_id              | bigint        | FK → accounts_taxes, nullable                |
| company_id               | bigint        | FK → companies, nullable                     |
| reconcile_id             | bigint        | FK → accounts_reconciles, nullable           |
| full_reconcile_id        | bigint        | FK → accounts_full_reconciles, nullable      |
| purchase_order_line_id   | bigint        | FK → purchases_order_lines, nullable         |
| creator_id               | bigint        | FK → users, nullable                         |
| created_at, updated_at   | timestamp     |                                              |

### 12.24. `accounts_accounts_move_line_taxes` (pivot)

| Column       | Type   | Constraint                                        |
| ------------ | ------ | ------------------------------------------------- |
| move_line_id | bigint | FK → accounts_account_move_lines, cascadeOnDelete |
| tax_id       | bigint | FK → accounts_taxes, cascadeOnDelete              |

### 12.25. `accounts_full_reconciles`

| Column                 | Type         | Constraint                            |
| ---------------------- | ------------ | ------------------------------------- |
| id                     | bigint, auto | PK                                    |
| exchange_move_id       | bigint       | FK → accounts_account_moves, nullable |
| creator_id             | bigint       | FK → users, nullable                  |
| created_at, updated_at | timestamp    |                                       |

### 12.26. `accounts_partial_reconciles`

| Column                 | Type          | Constraint                                 |
| ---------------------- | ------------- | ------------------------------------------ |
| id                     | bigint, auto  | PK                                         |
| amount                 | decimal(15,4) | nullable                                   |
| debit_amount_currency  | decimal(15,4) | nullable                                   |
| credit_amount_currency | decimal(15,4) | nullable                                   |
| max_date               | date          | nullable                                   |
| debit_move_id          | bigint        | FK → accounts_account_move_lines, nullable |
| credit_move_id         | bigint        | FK → accounts_account_move_lines, nullable |
| full_reconcile_id      | bigint        | FK → accounts_full_reconciles, nullable    |
| exchange_move_id       | bigint        | FK → accounts_account_moves, nullable      |
| debit_currency_id      | bigint        | FK → currencies, nullable                  |
| credit_currency_id     | bigint        | FK → currencies, nullable                  |
| company_id             | bigint        | FK → companies, nullable                   |
| creator_id             | bigint        | FK → users, nullable                       |
| created_at, updated_at | timestamp     |                                            |

### 12.27. `accounts_payment_registers`

| Column                      | Type          | Constraint                                   |
| --------------------------- | ------------- | -------------------------------------------- |
| id                          | bigint, auto  | PK                                           |
| communication               | string(255)   | nullable                                     |
| installments_mode           | string(255)   | nullable                                     |
| payment_type                | string(255)   | nullable                                     |
| partner_type                | string(255)   | nullable                                     |
| payment_difference_handling | string(255)   | nullable                                     |
| writeoff_label              | string(255)   | nullable                                     |
| payment_date                | date          | nullable                                     |
| amount                      | decimal(15,4) | nullable                                     |
| custom_user_amount          | decimal(15,4) | nullable                                     |
| source_amount               | decimal(15,4) | nullable                                     |
| source_amount_currency      | decimal(15,4) | nullable                                     |
| group_payment               | boolean       | default false                                |
| can_group_payments          | boolean       | default false                                |
| payment_token_id            | integer       | nullable                                     |
| currency_id                 | bigint        | FK → currencies, nullable                    |
| journal_id                  | bigint        | FK → accounts_journals, nullable             |
| partner_bank_id             | bigint        | FK → partners_bank_accounts, nullable        |
| custom_user_currency_id     | bigint        | FK → currencies, nullable                    |
| source_currency_id          | bigint        | FK → currencies, nullable                    |
| company_id                  | bigint        | FK → companies, nullable                     |
| partner_id                  | bigint        | FK → partners_partners, nullable             |
| payment_method_line_id      | bigint        | FK → accounts_payment_method_lines, nullable |
| writeoff_account_id         | bigint        | FK → accounts_accounts, nullable             |
| creator_id                  | bigint        | FK → users, nullable                         |
| created_at, updated_at      | timestamp     |                                              |

### 12.28. `accounts_account_payment_register_move_lines` (pivot)

| Column              | Type   | Constraint                                        |
| ------------------- | ------ | ------------------------------------------------- |
| payment_register_id | bigint | FK → accounts_payment_registers, cascadeOnDelete  |
| move_line_id        | bigint | FK → accounts_account_move_lines, cascadeOnDelete |

### 12.29. `accounts_accounts_move_reversals`

| Column                 | Type         | Constraint                       |
| ---------------------- | ------------ | -------------------------------- |
| id                     | bigint, auto | PK                               |
| reason                 | text         | nullable                         |
| date                   | date         |                                  |
| journal_id             | bigint       | FK → accounts_journals, nullable |
| company_id             | bigint       | FK → companies, cascadeOnDelete  |
| creator_id             | bigint       | FK → users, nullable             |
| created_at, updated_at | timestamp    |                                  |

### 12.30. Pivot Tables (Move Reversals)

- `accounts_accounts_move_reversal_move` — move_id ↔ reversal_id
- `accounts_accounts_move_reversal_new_move` — new_move_id ↔ reversal_id
- `accounts_accounts_move_payment` — invoice_id ↔ payment_id

---

## 13. MANUFACTURING PLUGIN (Webkul\Manufacturing)

### 13.1. `manufacturing_bills_of_materials`

| Column                       | Type          | Constraint                         |
| ---------------------------- | ------------- | ---------------------------------- |
| id                           | bigint, auto  | PK                                 |
| name                         | string(255)   |                                    |
| code                         | string(255)   | nullable                           |
| type                         | string(255)   | ('normal','phantom','subcontract') |
| active                       | boolean       | default true                       |
| quantity                     | decimal(15,4) | default 1                          |
| ready_to_produce             | string(255)   | default 'all_available'            |
| produce_delay                | integer       | default 0 (days)                   |
| days_to_prepare_mo           | integer       | default 0 (days)                   |
| allow_operation_dependencies | boolean       | default false                      |
| product_id                   | bigint        | FK → products_products             |
| uom_id                       | bigint        | FK → unit_of_measures              |
| company_id                   | bigint        | FK → companies, nullable           |
| creator_id                   | bigint        | FK → users, nullable               |
| deleted_at                   | timestamp     | nullable                           |
| created_at, updated_at       | timestamp     |                                    |

### 13.2. `manufacturing_work_centers`

| Column                 | Type            | Constraint               |
| ---------------------- | --------------- | ------------------------ |
| id                     | bigint, auto    | PK                       |
| sort                   | integer         |                          |
| name                   | string(255)     |                          |
| code                   | string(255)     | nullable, indexed        |
| working_state          | string(255)     | nullable                 |
| note                   | text            | nullable                 |
| time_efficiency        | decimal(5,2)    | nullable                 |
| default_capacity       | unsignedInteger | nullable                 |
| costs_per_hour         | decimal(15,4)   | nullable                 |
| setup_time             | decimal(15,4)   | nullable                 |
| cleanup_time           | decimal(15,4)   | nullable                 |
| oee_target             | decimal(5,2)    | nullable                 |
| color                  | string(255)     | nullable                 |
| company_id             | bigint          | FK → companies, nullable |
| calendar_id            | bigint          | FK → calendars, nullable |
| creator_id             | bigint          | FK → users, nullable     |
| deleted_at             | timestamp       | nullable                 |
| created_at, updated_at | timestamp       |                          |

### 13.3. `manufacturing_operations`

| Column                     | Type          | Constraint                                             |
| -------------------------- | ------------- | ------------------------------------------------------ |
| id                         | bigint, auto  | PK                                                     |
| sort                       | integer       | nullable                                               |
| name                       | string(255)   |                                                        |
| worksheet_type             | string(255)   | nullable                                               |
| worksheet                  | string(255)   | nullable                                               |
| worksheet_google_slide_url | string(255)   | nullable                                               |
| time_mode                  | string(255)   | nullable                                               |
| time_mode_batch            | integer       | nullable                                               |
| note                       | text          | nullable                                               |
| manual_cycle_time          | decimal(15,4) | nullable                                               |
| work_center_id             | bigint        | FK → manufacturing_work_centers                        |
| bill_of_material_id        | bigint        | FK → manufacturing_bills_of_materials, cascadeOnDelete |
| creator_id                 | bigint        | FK → users, nullable                                   |
| deleted_at                 | timestamp     | nullable                                               |
| created_at, updated_at     | timestamp     |                                                        |

### 13.4. `manufacturing_bill_of_material_lines`

| Column                 | Type          | Constraint                                             |
| ---------------------- | ------------- | ------------------------------------------------------ |
| id                     | bigint, auto  | PK                                                     |
| sort                   | integer       | nullable                                               |
| quantity               | decimal(15,4) | default 1                                              |
| is_manual_consumption  | boolean       | default false                                          |
| bill_of_material_id    | bigint        | FK → manufacturing_bills_of_materials, cascadeOnDelete |
| product_id             | bigint        | FK → products_products                                 |
| uom_id                 | bigint        | FK → unit_of_measures                                  |
| operation_id           | bigint        | FK → manufacturing_operations, nullable                |
| company_id             | bigint        | FK → companies, nullable                               |
| creator_id             | bigint        | FK → users, nullable                                   |
| created_at, updated_at | timestamp     |                                                        |

### 13.5. `manufacturing_bill_of_material_byproducts`

| Column                 | Type          | Constraint                                      |
| ---------------------- | ------------- | ----------------------------------------------- |
| id                     | bigint, auto  | PK                                              |
| sort                   | integer       | nullable                                        |
| quantity               | decimal(15,4) | default 1                                       |
| cost_share             | decimal(5,2)  | nullable                                        |
| bill_of_material_id    | bigint        | FK → manufacturing_bills_of_materials, nullable |
| product_id             | bigint        | FK → products_products                          |
| uom_id                 | bigint        | FK → unit_of_measures                           |
| operation_id           | bigint        | FK → manufacturing_operations, nullable         |
| company_id             | bigint        | FK → companies, nullable                        |
| creator_id             | bigint        | FK → users, nullable                            |
| created_at, updated_at | timestamp     |                                                 |

### 13.6. `manufacturing_orders`

| Column                  | Type          | Constraint                                      |
| ----------------------- | ------------- | ----------------------------------------------- |
| id                      | bigint, auto  | PK                                              |
| name                    | string(255)   | nullable, indexed                               |
| reference               | string(255)   | nullable, indexed                               |
| priority                | string(255)   | default '0'                                     |
| origin                  | string(255)   | nullable                                        |
| state                   | string(255)   | default 'draft', indexed                        |
| reservation_state       | string(255)   | nullable                                        |
| consumption             | string(255)   |                                                 |
| quantity                | decimal(15,4) | default 1                                       |
| quantity_producing      | decimal(15,4) | default 0                                       |
| product_uom_qty         | decimal(15,4) | default 0                                       |
| deadline_at             | timestamp     | nullable                                        |
| started_at              | timestamp     | useCurrent                                      |
| finished_at             | timestamp     | nullable                                        |
| is_planned              | boolean       | default false                                   |
| is_locked               | boolean       | default false                                   |
| product_id              | bigint        | FK → products_products                          |
| uom_id                  | bigint        | FK → unit_of_measures                           |
| producing_lot_id        | bigint        | FK → inventories_lots, nullable                 |
| operation_type_id       | bigint        | FK → inventories_operation_types                |
| source_location_id      | bigint        | FK → inventories_locations                      |
| destination_location_id | bigint        | FK → inventories_locations                      |
| final_location_id       | bigint        | FK → inventories_locations, nullable            |
| production_location_id  | bigint        | FK → inventories_locations, nullable            |
| procurement_group_id    | bigint        | FK → inventories_procurement_groups, nullable   |
| bill_of_material_id     | bigint        | FK → manufacturing_bills_of_materials, nullable |
| assigned_user_id        | bigint        | FK → users, nullable                            |
| order_point_id          | bigint        | FK → inventories_order_points, nullable         |
| company_id              | bigint        | FK → companies                                  |
| creator_id              | bigint        | FK → users, nullable                            |
| created_at, updated_at  | timestamp     |                                                 |

### 13.7. `manufacturing_work_orders`

| Column                  | Type          | Constraint                              |
| ----------------------- | ------------- | --------------------------------------- |
| id                      | bigint, auto  | PK                                      |
| name                    | string(255)   |                                         |
| sort                    | integer       | nullable                                |
| barcode                 | string(255)   | nullable, indexed                       |
| production_availability | string(255)   | nullable                                |
| state                   | string(255)   | default 'pending', indexed              |
| quantity_produced       | decimal(15,4) | nullable                                |
| expected_duration       | decimal(15,4) | nullable                                |
| started_at              | timestamp     | nullable                                |
| finished_at             | timestamp     | nullable                                |
| duration                | decimal(15,4) | nullable                                |
| duration_per_unit       | decimal(15,4) | nullable                                |
| duration_percent        | integer       | nullable                                |
| costs_per_hour          | decimal(15,4) | nullable                                |
| work_center_id          | bigint        | FK → manufacturing_work_centers         |
| product_id              | bigint        | FK → products_products, nullable        |
| uom_id                  | bigint        | FK → unit_of_measures                   |
| manufacturing_order_id  | bigint        | FK → manufacturing_orders               |
| calendar_leave_id       | bigint        | FK → calendar_leaves, nullable          |
| operation_id            | bigint        | FK → manufacturing_operations, nullable |
| creator_id              | bigint        | FK → users, nullable                    |
| created_at, updated_at  | timestamp     |                                         |

### 13.8. `manufacturing_unbuild_orders`

| Column                  | Type          | Constraint                                      |
| ----------------------- | ------------- | ----------------------------------------------- |
| id                      | bigint, auto  | PK                                              |
| name                    | string(255)   |                                                 |
| state                   | string(255)   | default 'draft', indexed                        |
| quantity                | decimal(15,4) | default 1                                       |
| product_id              | bigint        | FK → products_products                          |
| uom_id                  | bigint        | FK → unit_of_measures                           |
| bill_of_material_id     | bigint        | FK → manufacturing_bills_of_materials, nullable |
| manufacturing_order_id  | bigint        | FK → manufacturing_orders, nullable             |
| lot_id                  | bigint        | FK → inventories_lots, nullable                 |
| location_id             | bigint        | FK → inventories_locations                      |
| destination_location_id | bigint        | FK → inventories_locations                      |
| company_id              | bigint        | FK → companies                                  |
| creator_id              | bigint        | FK → users, nullable                            |
| created_at, updated_at  | timestamp     |                                                 |

### 13.9. Additional Manufacturing Tables

- **`manufacturing_batch_productions`** — Batch/lot production tracking
- **`manufacturing_consumption_warnings`** — Material consumption warnings
- **`manufacturing_consumption_warning_lines`** — Warning line items
- **`manufacturing_order_backorders`** — Manufacturing order backorders
- **`manufacturing_order_backorder_lines`** — Backorder line items
- **`manufacturing_order_split_batches`** — Split batch tracking
- **`manufacturing_order_splits`** — Order split records
- **`manufacturing_order_split_lines`** — Split line items (qty, scheduled_at)
- **`manufacturing_work_center_capacities`** — WC capacity (per product), UNIQUE(work_center_id, product_id)
- **`manufacturing_work_center_loss_types`** — Loss type catalog (UNIQUE loss_type)
- **`manufacturing_work_center_productivity_losses`** — Productivity losses
- **`manufacturing_work_center_productivity_logs`** — Productivity time logs
- **`manufacturing_work_center_tags`** — Work center tags
- **`manufacturing_work_center_tag`** — Pivot (work_center ↔ tag)
- **`manufacturing_work_center_alternatives`** — Alternative work centers (pivot)
- **`manufacturing_operation_dependencies`** — Operation dependencies (pivot)
- **`manufacturing_work_order_dependencies`** — Work order dependencies (pivot)
- **`manufacturing_bill_of_material_byproduct_attribute_values`** — BOM byproduct attribute values (pivot)
- **`manufacturing_bill_of_material_line_attribute_values`** — BOM line attribute values (pivot)
- **`manufacturing_operation_attribute_values`** — Operation attribute values (pivot)
- **`manufacturing_consumption_warning_order`** — Warning ↔ Order (pivot)
- **`manufacturing_order_backorder_order`** — Backorder ↔ Order (pivot)
- **`manufacturing_order_label_types`** — Order label types

---

## 14. OTHER PLUGINS

### 14.1. Fields (custom_fields)

| Column                 | Type         | Constraint    |
| ---------------------- | ------------ | ------------- |
| id                     | bigint, auto | PK            |
| model                  | string(255)  |               |
| field_type             | string(255)  |               |
| name                   | string(255)  |               |
| label                  | string(255)  |               |
| config                 | json         | nullable      |
| is_required            | boolean      | default false |
| is_active              | boolean      | default true  |
| sort                   | integer      | nullable      |
| created_at, updated_at | timestamp    |               |

### 14.2. Table Views

- **`table_views`** — Saved table views (filters, columns, sort config)
- **`table_view_favorites`** — User favorite views

### 14.3. Analytics (analytic_records)

| Column                 | Type          | Constraint                       |
| ---------------------- | ------------- | -------------------------------- |
| id                     | bigint, auto  | PK                               |
| name                   | string(255)   |                                  |
| date                   | date          |                                  |
| amount                 | decimal(15,4) |                                  |
| model_type             | string(255)   |                                  |
| model_id               | bigint        |                                  |
| company_id             | bigint        | FK → companies, nullable         |
| project_id             | bigint        | FK → projects_projects, nullable |
| task_id                | bigint        | FK → projects_tasks, nullable    |
| created_at, updated_at | timestamp     |                                  |

### 14.4. Projects

- **`projects_project_stages`** — Project stages
- **`projects_projects`** — Projects
- **`projects_milestones`** — Project milestones
- **`projects_tags`** — Tags
- **`projects_project_tag`** — Pivot
- **`projects_task_stages`** — Task stages
- **`projects_tasks`** — Tasks
- **`projects_task_users`** — Task assignments
- **`projects_task_tag`** — Pivot
- **`projects_user_project_favorites`** — User favorite projects

### 14.5. Recruitments

- **`recruitments_stages`** — Recruitment stages
- **`recruitments_stages_jobs`** — Pivot (stages ↔ job positions)
- **`recruitments_degrees`** — Degree catalog
- **`recruitments_refuse_reasons`** — Refuse reasons
- **`recruitments_applicant_categories`** — Applicant categories
- **`recruitments_candidates`** — Candidate profiles
- **`recruitments_candidate_skills`** — Candidate skills
- **`recruitments_candidate_applicant_categories`** — Pivot
- **`recruitments_applicants`** — Applicants
- **`recruitments_applicant_interviewers`** — Pivot
- **`recruitments_applicant_applicant_categories`** — Pivot
- **`recruitments_job_position_interviewers`** — Pivot

### 14.6. Time Off

- **`time_off_leave_types`** — Leave types
- **`time_off_leaves`** — Leave requests
- **`time_off_user_leave_types`** — Pivot (user allocation)
- **`time_off_leave_mandatory_days`** — Mandatory days
- **`time_off_leave_accrual_plans`** — Accrual plans
- **`time_off_leave_accrual_levels`** — Accrual levels
- **`time_off_leave_allocations`** — Leave allocations

### 14.7. Blogs

- **`blogs_categories`** — Blog categories
- **`blogs_posts`** — Blog posts
- **`blogs_tags`** — Tags
- **`blogs_post_tags`** — Pivot

### 14.8. Website

- **`website_pages`** — CMS pages

### 14.9. Payments

- **`payments_payment_methods`** — Payment gateway methods
- **`payments_payment_tokens`** — Stored payment tokens
- **`payments_payment_transactions`** — Transaction logs

---

## 15. CROSS-PLUGIN INTEGRATION TABLES

Integrasi antar-plugin dilakukan melalui foreign key tambahan di tabel utama:

| Source Plugin               | Target Table                     | Column Ditambahkan                             |
| --------------------------- | -------------------------------- | ---------------------------------------------- |
| Sales → Inventories         | `inventories_operations`         | `sale_order_id`                                |
| Sales → Inventories         | `inventories_moves`              | `sale_order_line_id`                           |
| Sales → Inventories         | `inventories_procurement_groups` | `sale_order_id`                                |
| Sales → Inventories         | `sales_orders`                   | `warehouse_id`                                 |
| Sales → Inventories         | `sales_order_lines`              | `warehouse_id`, `route_id`                     |
| Inventories → Purchases     | `purchases_orders`               | `operation_type_id`                            |
| Inventories → Purchases     | `purchases_order_lines`          | `final_location_id`, `order_point_id`          |
| Inventories → Purchases     | `inventories_moves`              | `purchase_order_line_id`                       |
| Inventories → Sales         | `sales_orders`                   | `warehouse_id`, `procurement_group_id`         |
| Inventories → Sales         | `sales_order_lines`              | `warehouse_id`, `route_id`                     |
| Purchases → Accounts        | `accounts_account_move_lines`    | `purchase_order_line_id`                       |
| Payments → Accounts         | `accounts_account_payments`      | `payment_token_id`, `payment_transaction_id`   |
| Manufacturing → Inventories | `inventories_moves`              | 11 FK (manufacturing_orders, work_orders, dll) |
| Manufacturing → Inventories | `inventories_move_lines`         | `work_order_id`, `order_id`                    |
| Manufacturing → Inventories | `inventories_warehouses`         | 11 FK manufacturing                            |
| Accounts → Partners         | `partners_partners`              | 7 FK accounting                                |
| Accounts → Products         | `products_categories`            | 3 FK accounting (income/expense/down_payment)  |

---

## 16. DATABASE RELATIONSHIP DIAGRAM (TEXTUAL)

```
users
 ├── creator_id → users (self-ref)
 ├── default_company_id → companies
 ├── partner_id → partners_partners
 └── teams ← user_team → teams

companies
 ├── parent_id → companies (self-ref)
 ├── currency_id → currencies
 ├── partner_id → partners_partners
 ├── state_id → states
 └── country_id → countries

partners_partners
 ├── parent_id → partners_partners (self-ref)
 ├── title_id → partners_titles
 ├── industry_id → partners_industries
 ├── state_id → states
 ├── country_id → countries
 ├── property_account_payable_id → accounts_accounts
 ├── property_account_receivable_id → accounts_accounts
 └── property_account_position_id → accounts_fiscal_positions

products_products
 ├── parent_id → products_products (self-ref)
 ├── category_id → products_categories
 ├── uom_id → unit_of_measures
 ├── uom_po_id → unit_of_measures
 ├── company_id → companies
 └── tags ← products_product_tag → products_tags

sales_orders
 ├── partner_id → partners_partners
 ├── partner_invoice_id → partners_partners
 ├── partner_shipping_id → partners_partners
 ├── currency_id → currencies
 ├── company_id → companies
 ├── user_id → users
 ├── team_id → sales_teams
 ├── fiscal_position_id → accounts_fiscal_positions
 ├── payment_term_id → accounts_payment_terms
 ├── journal_id → accounts_journals
 ├── warehouse_id → inventories_warehouses
 ├── procurement_group_id → inventories_procurement_groups
 └── lines → sales_order_lines → products_products

purchases_orders
 ├── partner_id → partners_partners
 ├── currency_id → currencies
 ├── company_id → companies
 ├── group_id → purchases_order_groups
 ├── requisition_id → purchases_requisitions
 ├── operation_type_id → inventories_operation_types
 ├── procurement_group_id → inventories_procurement_groups
 └── lines → purchases_order_lines → products_products

inventories_operations
 ├── operation_type_id → inventories_operation_types
 ├── source_location_id → inventories_locations
 ├── destination_location_id → inventories_locations
 ├── back_order_id → inventories_operations (self-ref)
 ├── return_id → inventories_operations (self-ref)
 ├── partner_id → partners_partners
 ├── sale_order_id → sales_orders
 ├── procurement_group_id → inventories_procurement_groups
 └── moves → inventories_moves → products_products

manufacturing_orders
 ├── product_id → products_products
 ├── bill_of_material_id → manufacturing_bills_of_materials
 ├── operation_type_id → inventories_operation_types
 ├── source_location_id → inventories_locations
 ├── destination_location_id → inventories_locations
 ├── procurement_group_id → inventories_procurement_groups
 ├── producing_lot_id → inventories_lots
 └── work_orders → manufacturing_work_orders → manufacturing_work_centers

accounts_account_moves
 ├── journal_id → accounts_journals
 ├── company_id → companies
 ├── partner_id → partners_partners
 ├── currency_id → currencies
 ├── fiscal_position_id → accounts_fiscal_positions
 ├── payment_term_id → accounts_payment_terms
 └── lines → accounts_account_move_lines → accounts_accounts
```

---

## 17. RINGKASAN STATISTIK

| Metrik                          | Jumlah                          |
| ------------------------------- | ------------------------------- |
| **Total migrasi**               | ±342                            |
| **Total tabel**                 | ±180                            |
| **Total pivot tables**          | ±35                             |
| **Tabel terbesar (kolom)**      | `inventories_moves` (40+ kolom) |
| **Tabel dengan FK terbanyak**   | `manufacturing_orders` (18 FK)  |
| **Tabel inti**                  | 19 (core system)                |
| **Tabel Support plugin**        | 22                              |
| **Tabel Security plugin**       | 3                               |
| **Tabel Partners plugin**       | 6                               |
| **Tabel Products plugin**       | 14                              |
| **Tabel Sales plugin**          | 14                              |
| **Tabel Purchases plugin**      | 9                               |
| **Tabel Inventories plugin**    | 29                              |
| **Tabel Manufacturing plugin**  | 30                              |
| **Tabel Employees plugin**      | 15                              |
| **Tabel Accounting (accounts)** | 30                              |
| **Tabel lainnya**               | 20+                             |
