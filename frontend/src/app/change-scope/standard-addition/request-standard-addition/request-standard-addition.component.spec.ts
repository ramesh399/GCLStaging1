import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RequestStandardAdditionComponent } from './request-standard-addition.component';

describe('RequestStandardAdditionComponent', () => {
  let component: RequestStandardAdditionComponent;
  let fixture: ComponentFixture<RequestStandardAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RequestStandardAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestStandardAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
