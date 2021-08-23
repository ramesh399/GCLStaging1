import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RequestUnitAdditionComponent } from './request-unit-addition.component';

describe('RequestUnitAdditionComponent', () => {
  let component: RequestUnitAdditionComponent;
  let fixture: ComponentFixture<RequestUnitAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RequestUnitAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestUnitAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
