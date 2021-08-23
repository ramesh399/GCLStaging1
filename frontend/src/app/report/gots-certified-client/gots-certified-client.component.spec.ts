import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GotsCertifiedClientComponent } from './gots-certified-client.component';

describe('GotsCertifiedClientComponent', () => {
  let component: GotsCertifiedClientComponent;
  let fixture: ComponentFixture<GotsCertifiedClientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GotsCertifiedClientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GotsCertifiedClientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
