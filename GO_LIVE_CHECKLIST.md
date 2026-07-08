# GO LIVE CHECKLIST

**Date:** 2026-07-08
**Status:** ❌ NOT READY

---

## T-MINUS 2 WEEKS

### Pre-Deployment
- [ ] All Critical bugs fixed and verified
- [ ] All High bugs fixed and verified
- [ ] All Medium bugs resolved or documented
- [ ] Security audit passed (no remaining Critical/High)
- [ ] Performance testing completed with acceptable results
- [ ] Database migration tested on staging
- [ ] Backup script created and tested
- [ ] Rollback script created and tested

### Environment
- [ ] Production server provisioned
- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] Redis installed and configured
- [ ] Supervisor configured for queue workers
- [ ] Cron entry added for scheduler
- [ ] Environment variables set on production
- [ ] File permissions configured (775 storage/)

### Testing
- [ ] All PHPUnit tests pass
- [ ] Playwright tests pass
- [ ] Manual smoke test on staging passed
- [ ] Load test passed (100 concurrent users minimum)
- [ ] Mobile responsiveness verified

### Monitoring
- [ ] Laravel Telescope installed and configured
- [ ] Error tracking (Sentry) configured
- [ ] Server monitoring set up
- [ ] Log rotation configured
- [ ] Health check endpoint created

---

## T-MINUS 1 WEEK

### Stakeholder Readiness
- [ ] School Admin trained on system
- [ ] Principal briefed on capabilities
- [ ] Teachers trained on core workflows
- [ ] Accountant trained on fee management
- [ ] HR trained on employee management
- [ ] User manuals distributed
- [ ] Support contact established

### Data Migration
- [ ] Student data migrated and verified
- [ ] Teacher data migrated and verified
- [ ] Fee structures set up for new academic year
- [ ] Academic year configured
- [ ] Class/section structure set up
- [ ] Timetable created
- [ ] Transport routes configured
- [ ] Library catalog imported

### Communication
- [ ] Staff notified of go-live date
- [ ] Parents informed about portal access
- [ ] Support team briefed on known issues
- [ ] Escalation matrix documented

---

## T-MINUS 1 DAY

### Final Checks
- [ ] Production deployment executed
- [ ] All caches cleared and re-cached
- [ ] Queue workers running
- [ ] Scheduler cron running
- [ ] Storage linked
- [ ] Email sending verified
- [ ] SMS sending verified (if configured)
- [ ] Backup runs successfully
- [ ] SSL certificate valid
- [ ] Domain resolves correctly
- [ ] Login works for all role types
- [ ] Dashboard loads for key roles

---

## T-HOUR (GO LIVE)

### Execution
- [ ] `php artisan down --retry=60`
- [ ] Final code deploy
- [ ] Run pending migrations
- [ ] Clear and rebuild caches
- [ ] `php artisan up`
- [ ] Verify site loads
- [ ] Monitor logs for errors (first 30 minutes)
- [ ] Monitor queue processing
- [ ] Monitor server resources

---

## T+1 WEEK (POST GO-LIVE)

### Monitoring
- [ ] Daily log review for first week
- [ ] Performance monitoring review
- [ ] Error tracking review
- [ ] User feedback collection
- [ ] Bug triage and prioritization
- [ ] Rollback plan if stability issues

### Stabilization
- [ ] Fix any Critical/High issues found post-launch
- [ ] Optimize slow queries identified in production
- [ ] Refine monitoring thresholds
- [ ] Document known issues and workarounds
- [ ] Schedule Phase 2 improvements
