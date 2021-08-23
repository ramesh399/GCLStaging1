import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddClientlogoChecklistHqComponent } from './add-clientlogo-checklist-hq.component';

describe('AddClientlogoChecklistHqComponent', () => {
  let component: AddClientlogoChecklistHqComponent;
  let fixture: ComponentFixture<AddClientlogoChecklistHqComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddClientlogoChecklistHqComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddClientlogoChecklistHqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
