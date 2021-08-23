import { TestBed } from '@angular/core/testing';

import { StandardService } from './standard.service';

describe('StandardService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: StandardService = TestBed.get(StandardService);
    expect(service).toBeTruthy();
  });
});
