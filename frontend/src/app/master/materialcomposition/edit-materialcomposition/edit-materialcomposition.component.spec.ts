import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditMaterialcompositionComponent } from './edit-materialcomposition.component';

describe('EditMaterialcompositionComponent', () => {
  let component: EditMaterialcompositionComponent;
  let fixture: ComponentFixture<EditMaterialcompositionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditMaterialcompositionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditMaterialcompositionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
