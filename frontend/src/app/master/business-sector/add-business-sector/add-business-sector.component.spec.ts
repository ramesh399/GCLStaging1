import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddBusinessSectorComponent } from './add-business-sector.component';

describe('AddBusinessSectorComponent', () => {
  let component: AddBusinessSectorComponent;
  let fixture: ComponentFixture<AddBusinessSectorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddBusinessSectorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddBusinessSectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
