.PHONY: help install test clean lint security docs

help:
	@echo "HuntFeed - Available commands:"
	@echo ""
	@echo "  make install          Install dependencies"
	@echo "  make test             Run all tests"
	@echo "  make test-websub      Run WebSub tests only"
	@echo "  make test-polling     Run polling tests only"
	@echo "  make lint             Check code style"
	@echo "  make security         Run security checks"
	@echo "  make clean            Clean temporary files"
	@echo "  make docs             Generate documentation"
	@echo ""

install:
	composer install

test:
	@echo "Running all tests..."
	php tests/QuickStartTest.php

test-websub:
	@echo "Running WebSub tests..."
	php tests/WebSubTest.php

test-polling:
	@echo "Running polling tests..."
	php tests/poling-test.php

lint:
	@echo "Checking code style..."
	@for file in $$(find src -name "*.php"); do \
		php -l $$file || exit 1; \
	done
	@echo "✅ No syntax errors found"

security:
	@echo "Running security checks..."
	@echo "Checking for dangerous functions..."
	@grep -r "eval\|exec\|system\|shell_exec" src/ || echo "✅ No dangerous functions found"
	@echo ""
	@echo "Checking for hardcoded secrets..."
	@grep -r "password.*=\|api.*key\|secret.*=" src/ || echo "✅ No hardcoded secrets found"

clean:
	@echo "Cleaning temporary files..."
	rm -rf .phpunit.result.cache
	rm -rf coverage/
	rm -f *.log
	rm -rf logs/*
	@echo "✅ Cleanup complete"

docs:
	@echo "Documentation files:"
	@echo "  - README.md            Main documentation (Persian)"
	@echo "  - README_EN.md         English documentation"
	@echo "  - WEBSUB_GUIDE.md      WebSub implementation guide"
	@echo "  - ARCHITECTURE.md      System architecture"
	@echo "  - SECURITY.md          Security policy"
	@echo "  - CONTRIBUTING.md      Contribution guidelines"
	@echo ""
	@echo "All documentation is in Markdown format"

setup-dev:
	@echo "Setting up development environment..."
	cp .env.example .env
	composer install
	chmod +x .githooks/pre-commit
	git config core.hooksPath .githooks
	@echo "✅ Development environment ready"

version:
	@grep '"version"' composer.json 2>/dev/null || echo "Version not specified in composer.json"

all: install test lint security
	@echo "✅ All checks passed!"
