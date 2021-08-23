import { TestBed } from '@angular/core/testing';

import { BrandListService } from './brand-list.service';

describe('BrandListService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: BrandListService = TestBed.get(BrandListService);
    expect(service).toBeTruthy();
  });
});
