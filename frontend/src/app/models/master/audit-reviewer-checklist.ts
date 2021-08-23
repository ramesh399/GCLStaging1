export interface AuditReviewerChecklist {
    id: number;
    name: string;
	code: string;
    guidance?:string;
    riskcategory:Array<any>;
    status?: number;
    riskCategoryList:Array<any>;
    risk_category_label?:string;  
    standard?:any;
    standard_label?:any;

}