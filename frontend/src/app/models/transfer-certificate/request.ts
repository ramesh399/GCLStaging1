export interface Request {
    id: number;
    app_id: number;
	unit_id: number;
	buyer_id: number;
    consignee_id: number;
    standard_id: number;
    purchase_order_number: string;   
    comments?: string;    
    transport_id: number;
    visible_to_brand: number;
    usda_nop_compliant: number;
    apeda_npop_compliant: number;
    unit_type:number;
}