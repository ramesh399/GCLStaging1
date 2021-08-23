import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditAudittypeComponent } from './edit-audittype.component';

describe('EditAudittypeComponent', () => {
  let component: EditAudittypeComponent;
  let fixture: ComponentFixture<EditAudittypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditAudittypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditAudittypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
