import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListMaterialcompositionComponent } from './list-materialcomposition.component';

describe('ListMaterialcompositionComponent', () => {
  let component: ListMaterialcompositionComponent;
  let fixture: ComponentFixture<ListMaterialcompositionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListMaterialcompositionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListMaterialcompositionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
