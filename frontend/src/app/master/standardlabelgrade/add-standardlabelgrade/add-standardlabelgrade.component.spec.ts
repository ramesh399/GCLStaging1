import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddStandardlabelgradeComponent } from './add-standardlabelgrade.component';

describe('AddStandardlabelgradeComponent', () => {
  let component: AddStandardlabelgradeComponent;
  let fixture: ComponentFixture<AddStandardlabelgradeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddStandardlabelgradeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddStandardlabelgradeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
