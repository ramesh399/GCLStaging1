import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditBusinessSectorGroupComponent } from './edit-business-sector-group.component';

describe('EditBusinessSectorGroupComponent', () => {
  let component: EditBusinessSectorGroupComponent;
  let fixture: ComponentFixture<EditBusinessSectorGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditBusinessSectorGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditBusinessSectorGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
