import { TestBed } from '@angular/core/testing';

import { AuditStandardService } from './audit-standard.service';

describe('AuditStandardService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: AuditStandardService = TestBed.get(AuditStandardService);
    expect(service).toBeTruthy();
  });
});
