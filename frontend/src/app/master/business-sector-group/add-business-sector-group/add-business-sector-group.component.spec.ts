import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddBusinessSectorGroupComponent } from './add-business-sector-group.component';

describe('AddBusinessSectorGroupComponent', () => {
  let component: AddBusinessSectorGroupComponent;
  let fixture: ComponentFixture<AddBusinessSectorGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddBusinessSectorGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddBusinessSectorGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
