import { TestBed } from '@angular/core/testing';

import { ReviewchecklistService } from './review-checklist.service';

describe('ReviewchecklistService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ReviewchecklistService = TestBed.get(ReviewchecklistService);
    expect(service).toBeTruthy();
  });
});
