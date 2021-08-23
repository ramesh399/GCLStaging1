export interface AuditPlanningChecklist {
    id: number;
    name: string;
	code: string;
    guidance?:string;
    riskcategory:Array<any>;
    status?: number;
    riskCategoryList:Array<any>;
    risk_category_label?:string;  
    audit_type_label?:any;
    audit_type?:any;
}