import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewMaterialcompositionComponent } from './view-materialcomposition.component';

describe('ViewMaterialcompositionComponent', () => {
  let component: ViewMaterialcompositionComponent;
  let fixture: ComponentFixture<ViewMaterialcompositionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewMaterialcompositionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewMaterialcompositionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
