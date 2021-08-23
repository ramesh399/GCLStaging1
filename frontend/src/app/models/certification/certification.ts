import { CertificationDetails } from "./certificationdetails";

export interface Certification {
    id: number;
    company_name: string;
	code: string;
    zipcode: string;
    city: string;
    app_status?:number;
    status?: number;
    invoice_id:number;
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
    units?: string;
    applicationreviews?: Array<any>;
	fees?: Array<any>;
    offer?: CertificationDetails;
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
    invoice_discount:number;
    invoice_grand_total_fee:number;
    invoice_number:string;
    feesEntries:Array<any>;
    expensesEntries:Array<any>;
    invoice_tax_amount:number;
    invoice_total_payable_amount:number;
    invoice_status:string;
    invoice_conversion_total_payable:number;

    paymentDetails:any;
    paymentDetails1:any;
    paymentStatusArr:any;

    arrEnumStatus?:any;
}