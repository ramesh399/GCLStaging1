import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddMandaycostComponent } from './add-mandaycost.component';

describe('AddMandaycostComponent', () => {
  let component: AddMandaycostComponent;
  let fixture: ComponentFixture<AddMandaycostComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddMandaycostComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddMandaycostComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
