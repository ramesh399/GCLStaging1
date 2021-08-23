import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListAudittypeComponent } from './list-audittype.component';

describe('ListAudittypeComponent', () => {
  let component: ListAudittypeComponent;
  let fixture: ComponentFixture<ListAudittypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListAudittypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListAudittypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
