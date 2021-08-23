import { TestBed } from '@angular/core/testing';

import { BrandGroupListService } from './brand-group-list.service';

describe('BrandGroupListService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: BrandGroupListService = TestBed.get(BrandGroupListService);
    expect(service).toBeTruthy();
  });
});
