import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RawMaterialStandardCombinationComponent } from './raw-material-standard-commination.component';

describe('RawMaterialStandardCombinationComponent', () => {
  let component: RawMaterialStandardCombinationComponent;
  let fixture: ComponentFixture<RawMaterialStandardCombinationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RawMaterialStandardCombinationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RawMaterialStandardCombinationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
