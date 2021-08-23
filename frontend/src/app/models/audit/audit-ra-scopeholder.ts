export interface AuditRaScopeHolder {
    id: number;
    audit_id: number;
    type_of_risk_id: number;
    type_of_risk_label: string;
    audit_type_id: number;
    audit_type_label: string;
    description_of_risk: string;
    potential_risks: string;
	measures_for_risk_reduction: string;
    frequency_of_risk: string;
    probability_rate: string;
    responsible_person: string;
}