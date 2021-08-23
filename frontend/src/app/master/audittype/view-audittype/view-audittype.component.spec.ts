import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ViewAudittypeComponent } from './view-audittype.component';

describe('ViewAudittypeComponent', () => {
  let component: ViewAudittypeComponent;
  let fixture: ComponentFixture<ViewAudittypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ViewAudittypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ViewAudittypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
