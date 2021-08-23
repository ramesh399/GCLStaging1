import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListClientlogoChecklistHqComponent } from './list-clientlogo-checklist-hq.component';

describe('ListClientlogoChecklistHqComponent', () => {
  let component: ListClientlogoChecklistHqComponent;
  let fixture: ComponentFixture<ListClientlogoChecklistHqComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListClientlogoChecklistHqComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListClientlogoChecklistHqComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
