import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddMaterialcompositionComponent } from './add-materialcomposition.component';

describe('AddMaterialcompositionComponent', () => {
  let component: AddMaterialcompositionComponent;
  let fixture: ComponentFixture<AddMaterialcompositionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddMaterialcompositionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddMaterialcompositionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
