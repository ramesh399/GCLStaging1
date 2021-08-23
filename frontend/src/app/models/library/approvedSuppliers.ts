export interface ApprovedSupplier {
    id: number;
    country_id: number;
    supplier_name: string;
    address: string;
    contact_person: string;
    email: string;
    phone: string;
    accreditation: string;
    certificate_no:string;
    scope_of_accreditation:string;
    accreditation_expiry_date:string;
    supplier_file:string;
    status?: number; 
}