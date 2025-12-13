# Security Policy

## Reporting Security Vulnerabilities

If you discover a security vulnerability in HuntFeed, please email us at **hosseinhunta@gmail.com** instead of using the issue tracker.

**Do not disclose the vulnerability publicly** until a fix has been released.

## Security Measures Implemented

### 1. Input Validation
- All feed URLs are validated before fetching
- Content is properly validated and sanitized
- User input is escaped appropriately

### 2. HTTPS/TLS Support
- WebSub communication requires HTTPS
- SSL certificate verification is enabled by default
- Configurable for development environments

### 3. HMAC-SHA1 Signature Verification
- All WebSub notifications are signed
- Signatures are verified using HMAC-SHA1
- Per-subscription secret keys (32-byte random)

### 4. Challenge-Response Verification
- Hub verification challenges are properly handled
- Challenge tokens are echoed back to confirm subscription
- Prevents unauthorized subscriptions

### 5. No Sensitive Data in Logs
- Secrets and API keys are not logged
- Sensitive information is excluded from debug output
- Consider implementing log sanitization

### 6. XML Parsing Safety
- SimpleXML is used with proper error handling
- XXE (XML External Entity) attacks are prevented
- Entity loading is disabled

### 7. Error Handling
- Exceptions are handled gracefully
- Error messages don't leak sensitive information
- Stack traces are not exposed in production

## Best Practices for Users

### Development
- Use `.env` file for sensitive configuration
- Never commit `.env` file to version control
- Use `.env.example` as a template
- Enable `APP_DEBUG=false` in production

### Production
- Enable HTTPS enforcement (`HTTPS_REQUIRED=true`)
- Use strong, random secrets for WebSub
- Implement database persistence for subscriptions
- Monitor logs for suspicious activity
- Keep PHP and dependencies updated
- Implement rate limiting on callback endpoint
- Validate callback URL ownership

### WebSub Security
- Only subscribe to trusted hubs
- Verify hub URLs before subscription
- Implement subscription expiration (lease renewal)
- Monitor notification delivery for anomalies
- Log all WebSub events

## Security Updates

We take security seriously and will:
1. Acknowledge receipt of vulnerability reports within 48 hours
2. Provide updates within 90 days or sooner for critical issues
3. Disclose vulnerabilities responsibly
4. Credit reporters (unless they prefer anonymity)

## Dependency Security

- We use Composer for dependency management
- Run `composer update` regularly to get security patches
- Review security advisories at https://security.snyk.io/
- Monitor dependencies for known vulnerabilities

## Recommended Deployment Checklist

- [ ] Use HTTPS with valid SSL certificate
- [ ] Set `APP_DEBUG=false`
- [ ] Configure `.env` with production values
- [ ] Implement database for subscription storage
- [ ] Set up log rotation and monitoring
- [ ] Configure firewall rules
- [ ] Implement rate limiting
- [ ] Set up backup procedures
- [ ] Enable security monitoring
- [ ] Keep PHP updated to latest stable version
- [ ] Keep all dependencies updated
- [ ] Regular security audits

## Third-Party Dependencies

This project has minimal dependencies:
- **PHP 8.1+** - Language requirement
- No external libraries for core functionality
- Composer manages all PHP dependencies

## Compliance

HuntFeed aims to comply with:
- OWASP Top 10 guidelines
- PSR-12 coding standards
- W3C WebSub specification
- Industry best practices

## Contact

For security concerns, please contact: **hosseinhunta@gmail.com**

Thank you for helping keep HuntFeed secure! 🔒

