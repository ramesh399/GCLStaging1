import { OfferDetails } from "./offerdetails";

export interface Offer {
    id: number;
    company_name: string;
	code: string;
    zipcode: string;
    city: string;
    app_status?:number;
    status?: number;
manday?: any;
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

    telephone?: string;
    email_address?: string;
    standards?: string;
    products?: string;
    units?: any;
    applicationreviews?: Array<any>;
	fees?: Array<any>;
    offer?: OfferDetails;
	offer_currency_code?: string;
	tax_percentage?: string;
    applicationunitreviews?: Array<any>;   
    currency:string;
    current_date:string;
    mandays:string;
    offer_certification_fee:string;
    offer_other_expenses:any;
    offerhistory:Array<any>;
    offerenumstatus:Array<any>;
    standard_ids?:any;
    approve_comment?:any;
    reject_comment?:any;

    download_files?:any;
    showChemicalList?:any;
    showEnvironmentalReport?:any;
    showEnvironmentalDeclaration?:any;
    showSocialDeclaration?:any;
    showChemicalDeclaration?:any;
    showCCS?:any;
    scheme_files?:any;
    processor_files?:any;
    standard_files?:any;
    implementation_files?:any;
    checklist_files?:any;
    standard_codes?:any;
    
    can_edit_offer?:any;
    can_send_to_hq?:any;
    reinitiate_comment?:any;
    can_send_back_oss_customer?:any;
    can_approve_reject?:any;
    
}