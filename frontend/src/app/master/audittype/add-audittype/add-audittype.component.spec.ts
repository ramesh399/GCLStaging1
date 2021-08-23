import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAudittypeComponent } from './add-audittype.component';

describe('AddAudittypeComponent', () => {
  let component: AddAudittypeComponent;
  let fixture: ComponentFixture<AddAudittypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddAudittypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddAudittypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
