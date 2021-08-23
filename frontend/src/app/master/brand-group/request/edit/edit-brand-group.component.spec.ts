import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditBrandGroupComponent } from './edit-brand-group.component';

describe('EditBrandGroupComponent', () => {
  let component: EditBrandGroupComponent;
  let fixture: ComponentFixture<EditBrandGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditBrandGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditBrandGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
