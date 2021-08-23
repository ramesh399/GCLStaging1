import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddUnitAdditionComponent } from './add-unit-addition.component';

describe('AddUnitAdditionComponent', () => {
  let component: AddUnitAdditionComponent;
  let fixture: ComponentFixture<AddUnitAdditionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddUnitAdditionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddUnitAdditionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
