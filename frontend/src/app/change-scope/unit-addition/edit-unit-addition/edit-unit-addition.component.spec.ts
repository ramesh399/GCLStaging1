import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditUnitAdditionComponent } from './edit-unit-addition.component';

describe('EditUnitAdditionComponent', () => {
  let component: EditUnitAdditionComponent;
  let fixture: ComponentFixture<EditUnitAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditUnitAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditUnitAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
