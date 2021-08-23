import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditStandardlabelgradeComponent } from './edit-standardlabelgrade.component';

describe('EditStandardlabelgradeComponent', () => {
  let component: EditStandardlabelgradeComponent;
  let fixture: ComponentFixture<EditStandardlabelgradeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditStandardlabelgradeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditStandardlabelgradeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
