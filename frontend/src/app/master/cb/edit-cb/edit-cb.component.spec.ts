import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditCbComponent } from './edit-cb.component';

describe('EditCbComponent', () => {
  let component: EditCbComponent;
  let fixture: ComponentFixture<EditCbComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditCbComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditCbComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
