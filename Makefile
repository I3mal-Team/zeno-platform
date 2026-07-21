.DEFAULT_GOAL := help
DC := docker compose
BE := $(DC) exec -T app

help: ## عرض الأوامر
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-16s\033[0m %s\n",$$1,$$2}'

# ── الخدمات ────────────────────────────────────────
up:        ## تشغيل كل الخدمات
	$(DC) up -d
down:      ## إيقاف الخدمات
	$(DC) down
restart:   ## إعادة تشغيل
	$(DC) restart
ps:        ## حالة الخدمات
	$(DC) ps
logs:      ## متابعة السجلات (s=service)
	$(DC) logs -f $(s)
shell:     ## دخول حاوية التطبيق
	$(DC) exec app sh

# ── الباك إند ──────────────────────────────────────
art:       ## artisan (c="migrate --seed")
	$(BE) php artisan $(c)
migrate:   ## تشغيل المايجريشن
	$(BE) php artisan migrate
fresh:     ## إعادة بناء القاعدة + البذور
	$(BE) php artisan migrate:fresh --seed
seed:      ## البذور فقط
	$(BE) php artisan db:seed
tinker:    ## Tinker
	$(DC) exec app php artisan tinker
composer:  ## composer (c="require spatie/...")
	$(BE) composer $(c)

# ── الجودة ─────────────────────────────────────────
test:      ## كل الاختبارات
	$(BE) ./vendor/bin/pest
test-arch: ## اختبارات المعمارية فقط (القواعد الأربع)
	$(BE) ./vendor/bin/pest tests/Architecture
pint:      ## تنسيق الكود
	$(BE) ./vendor/bin/pint
stan:      ## تحليل ساكن
	$(BE) ./vendor/bin/phpstan analyse --memory-limit=1G
check:     ## pint + stan + pest — نفس ما يشغّله CI
	$(MAKE) pint && $(MAKE) stan && $(MAKE) test

# ── قاعدة البيانات ─────────────────────────────────
psql:      ## صدفة psql
	$(DC) exec postgres psql -U zeno -d zeno
db-tables: ## عرض الجداول
	$(DC) exec -T postgres psql -U zeno -d zeno -c "\dt"

# ── الموبايل ───────────────────────────────────────
m-get:     ## جلب حزم فلاتر
	cd mobile && fvm flutter pub get
m-run:     ## تشغيل التطبيق
	cd mobile && fvm flutter run
m-test:    ## اختبارات فلاتر
	cd mobile && fvm flutter test
m-analyze: ## تحليل فلاتر
	cd mobile && fvm flutter analyze

.PHONY: help up down restart ps logs shell art migrate fresh seed tinker composer \
        test test-arch pint stan check psql db-tables m-get m-run m-test m-analyze
