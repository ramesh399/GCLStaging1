import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RawMaterialStandardComponent } from './raw-material-standard.component';

describe('RawMaterialStandardComponent', () => {
  let component: RawMaterialStandardComponent;
  let fixture: ComponentFixture<RawMaterialStandardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RawMaterialStandardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RawMaterialStandardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
