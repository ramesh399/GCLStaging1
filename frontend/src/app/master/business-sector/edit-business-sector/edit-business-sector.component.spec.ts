import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditBusinessSectorComponent } from './edit-business-sector.component';

describe('EditBusinessSectorComponent', () => {
  let component: EditBusinessSectorComponent;
  let fixture: ComponentFixture<EditBusinessSectorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditBusinessSectorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditBusinessSectorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
