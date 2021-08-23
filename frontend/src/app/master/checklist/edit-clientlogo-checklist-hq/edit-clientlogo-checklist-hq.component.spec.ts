import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditClientlogoChecklistHqComponent } from './edit-clientlogo-checklist-hq.component';

describe('EditClientlogoChecklistHqComponent', () => {
  let component: EditClientlogoChecklistHqComponent;
  let fixture: ComponentFixture<EditClientlogoChecklistHqComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditClientlogoChecklistHqComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditClientlogoChecklistHqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
