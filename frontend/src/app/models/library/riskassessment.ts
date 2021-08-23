export interface Riskassessment {
    id: number;
    franchise_id: number;
    franchise_label?:any;
    threat_id: string;
    threat_label?:any;
	vulnerability?: string;
	probability?: string;
	impact?: string;
    risk_value?: string;    
    controls?:any;
    created_at?:any;
}