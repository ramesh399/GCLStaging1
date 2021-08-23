import { InvoiceDetails } from "./invoicedetails";

export interface Invoice {
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
    offer?: InvoiceDetails;
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
    invoice?:any;
    
    customer_id?:any;
    credit_note_option?:any;
    franchise_id?:any;
    canUpdatePaymentStatus?:any;
    canGenerateInvoice?:any;
    canDoInvoiceApproval?:any;
    canSubmitForInvoiceApproval?:any;
    franchise_details?:any;
    rejected_by?:any;
    rejected_date?:any;
    rejected_comments?:any;
    oss_payment_details?:any;
    reject_comments?:any;
    enumStatus?:any;
    invoice_status_name?:any;
    currency_code?:any;
    conversion_rate?:any;
    conversion_currency_code?:any;
}