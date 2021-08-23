import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditProducttypeComponent } from './edit-producttype.component';

describe('EditProducttypeComponent', () => {
  let component: EditProducttypeComponent;
  let fixture: ComponentFixture<EditProducttypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditProducttypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditProducttypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
