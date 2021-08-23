import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditItCenterComponent } from './edit-it-center.component';

describe('EditItCenterComponent', () => {
  let component: EditItCenterComponent;
  let fixture: ComponentFixture<EditItCenterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditItCenterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditItCenterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
