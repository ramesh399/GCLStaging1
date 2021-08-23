export interface ClientInformationChecklist {
    id: number;
    name: string;
	code: string;
    interpretation?:string;
    riskcategory:Array<any>;
    status?: number;
    riskCategoryList:Array<any>;
    risk_category_label?:string;  
    process?:any;
    process_label?:any;
    standard?:any;
    standard_label?:any;

}