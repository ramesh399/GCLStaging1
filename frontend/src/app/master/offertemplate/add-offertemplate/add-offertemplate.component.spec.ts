import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddOffertemplateComponent } from './add-offertemplate.component';

describe('AddOffertemplateComponent', () => {
  let component: AddOffertemplateComponent;
  let fixture: ComponentFixture<AddOffertemplateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddOffertemplateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddOffertemplateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
