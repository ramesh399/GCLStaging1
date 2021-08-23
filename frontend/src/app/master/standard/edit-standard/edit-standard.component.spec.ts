import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditStandardComponent } from './edit-standard.component';

describe('EditStandardComponent', () => {
  let component: EditStandardComponent;
  let fixture: ComponentFixture<EditStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
