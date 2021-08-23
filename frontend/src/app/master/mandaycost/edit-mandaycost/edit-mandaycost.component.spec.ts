import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditMandaycostComponent } from './edit-mandaycost.component';

describe('EditMandaycostComponent', () => {
  let component: EditMandaycostComponent;
  let fixture: ComponentFixture<EditMandaycostComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditMandaycostComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditMandaycostComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
