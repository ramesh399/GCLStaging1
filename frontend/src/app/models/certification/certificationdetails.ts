﻿export interface CertificationDetails {
    id?: number;
    currency: string;
    certification_fee_sub_total:string;
    other_expense_sub_total:string;
    total:number;
    gst_rate:number;
    total_payable_amount:number;
    conversion_currency_code:string;
    conversion_rate:number;
    conversion_total_payable:number;
    offer_status:number;
    discount:number;
    grand_total_fee:number;
    taxname:string;
    offerhistory:Array<any>;
    conversion_required_status:number;
    tax_percentage:number;
    offer_id:number;
	invoice_discount:any;
}