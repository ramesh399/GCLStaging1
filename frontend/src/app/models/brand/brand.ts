export interface Application {
    id: number;
    company_name: string;
	code: string;
    zipcode: string;
    city: string;
    app_status?:number;
    status?: number;

    created_at?: string;
    preferred_partner_id_name?: string;
    address?: string;
    country_id_name?: string;
    state_id_name?: string;
    salutation_name?: string;
    title?: string;
    first_name?: string;
    last_name?: string;
    job_title?: string;
    manday?: any;
    telephone?: string;
    email_address?: string;
    standards?: string;
    products?: string;
    units?: any;
    applicationreviews?: Array<any>;
	fees?: Array<any>;
    offer?: Array<any>;
	offer_currency_code?: string;
	tax_percentage?: number;
    applicationunitreviews?: Array<any>;  
    other_expenses?: Array<any>;	
    offercode?:string;
	offer_status?:number;
	discount?:number;
    taxname?:string;
    mandays:number;
    applicationapprovals:any;
	conversion_required_status:number;
	conversion_rate:any;
	currency:any;
    conversion_currency_code:any;   
    company_file:string;
    approverid:number;
    reviewerid:number;
    hasapprover:number;
    hasreviewer:number;
    appunitmanday?: Array<any>;	
    franchise?:any;
    productDetails?:any;
    franchise_id?:any;
    rejected_date?:any;
    reject_comment:any;
    applicationchecklistcmt?:any;
    audit_type?:any;
    process_id?:any;
    parent_app_id?:any;
    unit_addition_id?:any;
    addition_id?:any;
    new_units?:any;
    showedit_view?:any;
    applicationcertifiedbyothercb?:any;
    tax_no?:any;
    company_website?:any;
    offerenumstatus?:any;
    canChangeMaterialComp?:any;
    showApplicationReview?:any;
    showApplicationApprove?:any;
    standard_ids?:any;

    created_by?:any;
    audit_type_label?:any;
	
	brand_id?: number;
	brand_name? : string;
	brand_number? : string;
	brand_version? : string;
    brand_group? :string;
    
	

}