# Release Guide

## How to Create a Release

### 1. Prepare Release Branch
```bash
git checkout main
git pull origin main
git checkout -b release/v1.0.0
```

### 2. Update Version Numbers

Update version in these files:
- `composer.json` (version field if exists)
- `CHANGELOG.md` - Add release notes
- Any documentation mentioning version

### 3. Run Tests

```bash
# Run all tests
php tests/QuickStartTest.php

# Run WebSub tests
php tests/WebSubTest.php

# All must pass with 100%
```

### 4. Update Documentation

- [ ] Update CHANGELOG.md with release notes
- [ ] Update README.md if needed
- [ ] Update SECURITY.md if applicable
- [ ] Review and update API documentation

### 5. Commit and Push

```bash
git add .
git commit -m "Release: v1.0.0 - [Release notes summary]"
git push origin release/v1.0.0
```

### 6. Create Pull Request

- Create PR from `release/v1.0.0` to `main`
- Add release notes to PR description
- Request review from maintainers
- Once approved, merge

### 7. Create GitHub Release

```bash
# Tag the release
git checkout main
git pull origin main
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

Or through GitHub UI:
1. Go to Releases page
2. Click "Draft a new release"
3. Choose tag version
4. Add release title and notes
5. Click "Publish release"

### 8. Publish to Packagist

- Update Packagist if not auto-updating
- Verify package availability

## Release Checklist

- [ ] All tests passing
- [ ] CHANGELOG.md updated
- [ ] Documentation updated
- [ ] Version numbers updated
- [ ] No breaking changes (or documented)
- [ ] Code reviewed
- [ ] Release notes written
- [ ] Tagged in git
- [ ] Packagist updated

## Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR.MINOR.PATCH** (e.g., 1.0.0)
- **MAJOR** - Incompatible API changes
- **MINOR** - New functionality (backwards compatible)
- **PATCH** - Bug fixes

## Release Types

### Major Release (1.0.0)
- Significant new features
- Breaking changes
- Major refactoring
- Major performance improvements

### Minor Release (1.1.0)
- New features (backwards compatible)
- Enhancements
- Performance improvements

### Patch Release (1.0.1)
- Bug fixes
- Security patches
- Minor improvements

## Release Notes Template

```markdown
## v1.0.0 - Release Title

### 🎉 New Features
- Feature 1
- Feature 2

### 🔧 Improvements
- Improvement 1
- Improvement 2

### 🐛 Bug Fixes
- Bug fix 1
- Bug fix 2

### ⚠️ Breaking Changes
- Change 1
- Change 2

### 📚 Documentation
- Doc update 1
- Doc update 2

### 🙏 Contributors
- @contributor1
- @contributor2
```

## Hotfix Releases

For critical bugs in production:

```bash
# Create hotfix branch from main
git checkout -b hotfix/v1.0.1 main

# Make fix
# Test thoroughly
# Update version and CHANGELOG

# Merge back to main and develop
git checkout main
git merge --no-ff hotfix/v1.0.1
git tag -a v1.0.1 -m "Hotfix v1.0.1"

git checkout develop
git merge --no-ff hotfix/v1.0.1
```

## Release Communication

After release:

1. Announce on:
   - GitHub Releases page
   - Project documentation
   - Community channels

2. Highlight:
   - New features
   - Improvements
   - Breaking changes (with migration guide)

## Monitoring Post-Release

- Monitor issue reports
- Watch for reported bugs
- Check usage patterns
- Gather feedback

## Deprecation Policy

- Announce deprecations at least 2 releases in advance
- Provide migration path
- Keep deprecated features working for 2 major versions
- Mark deprecated code with `@deprecated` annotations

