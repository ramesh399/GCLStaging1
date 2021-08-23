export interface OfferDetails {
    id?: number;
    currency: string;
    certification_fee_sub_total:string;
    other_expense_sub_total:string;
    total:string;
    gst_rate:string;
    total_payable_amount:string;
    conversion_currency_code:string;
    conversion_rate:string;
    conversion_total_payable:string;
    offer_status:number;
    discount:number;
    grand_total_fee:number;
    taxname:string;
    offerhistory:Array<any>;
    conversion_required_status:number;
    tax_percentage:number;
    offer_code:string;
    updated_at:string;
    quotation_file?:any;
    processor_agreement_file?:any;
    offerlist_id?:any;
    scheme_rules_file?:any;
    processor_files?:any;
    audit_report_file?:any;
    risk_assessment_file?:any;
    reconciliation_report_file?:any;
    volume_reconciliation_formula?:any;
    content_claim_standard_file?:any;
    chemical_declaration_file?:any;
    social_declaration_file?:any;
    environmental_declaration_file?:any;
    environmental_report_file?:any;
    chemical_list_file?:any;
    
}