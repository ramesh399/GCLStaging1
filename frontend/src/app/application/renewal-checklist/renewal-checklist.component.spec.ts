import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RenewalChecklistComponent } from './renewal-checklist.component';

describe('RenewalChecklistComponent', () => {
  let component: RenewalChecklistComponent;
  let fixture: ComponentFixture<RenewalChecklistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RenewalChecklistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RenewalChecklistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
