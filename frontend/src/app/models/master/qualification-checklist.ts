export interface QualificationChecklist {
    id: number;
    name: string;
	code: string;
    description: string;
    product_type_id:number;
    material_composition:string;
    productStandardList:Array<any>;
    status?: number;
    wastage?: string;
    recurringPeriodList:Array<any>;
    question?:any;
    file_upload_required?:any;
    standard_ids?:any;
    role_ids?:any;
    recurring_period?:any;
    answer?:number;
    comment:string;
    file:string;
    new_valid_until:any;
    valid_until?:string;
    currentdate?:string;
    role_label?:string;
    standard_label?:string;
    process_label?:string;
    guidance?:string;
    recurring_period_label?:string;
    file_upload_required_label?:string;
    business_sector_group_ids?:any;
    bsector_label?:any;
    bsectorgroup_label?:any;
    
}