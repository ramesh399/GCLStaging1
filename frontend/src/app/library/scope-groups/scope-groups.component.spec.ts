import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ScopeGroupsComponent } from './scope-groups.component';

describe('ScopeGroupsComponent', () => {
  let component: ScopeGroupsComponent;
  let fixture: ComponentFixture<ScopeGroupsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ScopeGroupsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ScopeGroupsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
