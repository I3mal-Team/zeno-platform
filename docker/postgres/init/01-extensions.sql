-- إضافات إلزامية للسكيما (راجع docs/10-domain-model-and-database-schema.md §0.2)

-- البحث النصف قطري — جوهر المنتج
CREATE EXTENSION IF NOT EXISTS postgis;

-- gen_random_uuid() لأعمدة uuid
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- بحث تقريبي على النص العربي (احتياطي إلى جانب Meilisearch)
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- إزالة التشكيل والهمزات — يدعم ArabicNormalizer
CREATE EXTENSION IF NOT EXISTS unaccent;
