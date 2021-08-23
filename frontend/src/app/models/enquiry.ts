import { User } from "./master/user";

export interface Enquiry {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    
    country_id:number;
    state_id:number;
    date_of_birth:string;
    user_type:number;
    company_name:string;
    contact_name:string;
    company_telephone:string;
    company_email:string;
    company_website:string;
    company_address1:string;
    company_address2:string;
    company_city:string;
    company_zipcode:string;
    company_country_id:string;
    company_state_id:string;
    number_of_employees:string;
    number_of_sites:string;
    description:string;
    other_information:string;
    standards:string;
    telephone?: string;
    status:string;
    enquirystatus:string;
    status_updated_date:string;
    status_updated_by:string;
    status_updated_by_name:string;
    franchise_id?: User;
    franchise:string;
    phone_code:string;
    company_phone_code:string;
    customer_id:string;
    updated_at:string;
    status_id?:number;
    ip_address?:any;
    created_at?:any;
    
}