import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditOffertemplateComponent } from './edit-offertemplate.component';

describe('EditOffertemplateComponent', () => {
  let component: EditOffertemplateComponent;
  let fixture: ComponentFixture<EditOffertemplateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditOffertemplateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditOffertemplateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
