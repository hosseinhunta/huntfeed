# Contributing to HuntFeed

Thank you for your interest in contributing to HuntFeed! We welcome contributions from the community.

## How to Contribute

### 1. Fork the Repository
- Click the "Fork" button on the GitHub repository page
- Clone your forked repository locally

```bash
git clone https://github.com/YOUR_USERNAME/huntfeed.git
cd huntfeed
```

### 2. Create a Feature Branch
Create a new branch for your feature or fix:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 3. Make Your Changes
- Write clean, maintainable code
- Follow PSR-12 coding standards
- Add comments for complex logic
- Include tests for new features

### 4. Test Your Changes
Run the comprehensive test suite:

```bash
php tests/QuickStartTest.php
php tests/WebSubTest.php
```

All tests must pass before submission.

### 5. Commit Your Changes
Write clear, descriptive commit messages:

```bash
git commit -m "Add: feature description" 
# or
git commit -m "Fix: bug description"
```

### 6. Push to Your Fork
```bash
git push origin feature/your-feature-name
```

### 7. Create a Pull Request
- Go to the original repository
- Click "New Pull Request"
- Select your branch
- Add a clear description of changes
- Submit the pull request

## Development Guidelines

### Code Standards
- Use PHP 8.1+ syntax
- Follow PSR-12 coding standards
- Use type hints for all parameters and return types
- Write self-documenting code

### Testing
- Write tests for all new features
- Ensure existing tests pass
- Aim for high code coverage
- Test edge cases and error conditions

### Documentation
- Update relevant documentation files
- Add inline code comments for complex logic
- Include examples for new features
- Update README if needed

### Security
- Never commit sensitive information (API keys, passwords, tokens)
- Use `.env.example` for configuration templates
- Validate and sanitize all inputs
- Follow OWASP security guidelines

## Architecture

### Project Structure
```
src/
  ├── Engine/          # Core engine classes
  ├── Event/           # Event handling
  ├── Feed/            # Feed and FeedItem classes
  ├── Hub/             # FeedManager and collection classes
  ├── Parser/          # Feed parsers (RSS, Atom)
  ├── Security/        # Security-related classes
  ├── Storage/         # Storage interfaces
  ├── Transport/       # Feed fetching (FeedFetcher)
  ├── Utils/           # Utility functions
  └── WebSub/          # WebSub (PubSubHubbub) implementation

tests/
  ├── QuickStartTest.php        # Comprehensive test suite
  ├── WebSubTest.php            # WebSub feature tests
  └── poling-test.php           # Polling tests

examples/
  ├── WebSubExample.php         # WebSub usage examples
  └── callback.php              # WebSub callback endpoint template

Documentation/
  ├── README.md                 # Main documentation
  ├── WEBSUB_GUIDE.md          # WebSub implementation guide
  └── ARCHITECTURE.md          # Architecture details
```

### Key Components

1. **FeedManager** - Manages multiple feeds and items
2. **FeedFetcher** - Fetches and parses feed content
3. **WebSubManager** - Handles WebSub subscriptions
4. **WebSubSubscriber** - Core WebSub subscription logic
5. **WebSubHandler** - HTTP endpoint for notifications

## Issue Reporting

When reporting an issue:
1. Check if it's already reported
2. Include a clear description
3. Provide steps to reproduce
4. Include error messages and logs
5. Specify your environment (PHP version, OS, etc.)

## Feature Requests

For feature requests:
1. Check if already requested
2. Provide a clear use case
3. Explain the expected behavior
4. Suggest implementation approach if possible

## Questions?

- Read the [WEBSUB_GUIDE.md](WEBSUB_GUIDE.md) for WebSub documentation
- Check [ARCHITECTURE.md](ARCHITECTURE.md) for system design
- Open an issue for discussions

Thank you for contributing to HuntFeed! 🙏

