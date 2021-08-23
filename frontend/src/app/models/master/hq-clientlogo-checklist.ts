export interface HqClientlogoChecklist {
    id: number;
    name: string;
	code: string;
    interpretation?:string;
    finding_id:Array<any>;
    status?: number;
    riskCategoryList:Array<any>;
    risk_category_label?:string;  
    standard?:any;
    standard_label?:any;
}