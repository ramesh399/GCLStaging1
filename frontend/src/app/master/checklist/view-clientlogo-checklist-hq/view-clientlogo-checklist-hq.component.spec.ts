import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewClientlogoChecklistHqComponent } from './view-clientlogo-checklist-hq.component';

describe('ViewClientlogoChecklistHqComponent', () => {
  let component: ViewClientlogoChecklistHqComponent;
  let fixture: ComponentFixture<ViewClientlogoChecklistHqComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewClientlogoChecklistHqComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewClientlogoChecklistHqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
