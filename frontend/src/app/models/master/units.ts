import { Product } from './product';
import { Process } from './process';
import { Standard } from './standard';
import { BusinessSector } from './business-sector';

export interface Units {
    id ?: number;
    unit_id ?: number;
    unit_name: string;
    code ?:string;
	unit_address: string;
    unit_zipcode: string;
    unit_country_id:number;
    unit_state_id:number;
    unit_city:string;
    unit_country_name:string;
    unit_state_name:string;
    no_of_employees:string;
    business_sector_id?:BusinessSector[];
    unit_product_id?:string;
    sel_process?:Process[];
    sel_product?:Product[];
    unitStateList?:any[];
    certFile?:Array<any>;
    sel_standard?:Array<any>;
    status?: number;
    selUnitStandardList:Standard[];
    chk_company_process_unit?:string;
    unit_type?:number;
    unitProductList?:Array<any>;
    bsectorList?:any;
    processList?:any;
    addition_type?:any;
    unit_exists?:any;
    business_sector_exists?:any;
    sel_processexists?:any;
    deleted?:any;
    sel_reduction?:any;
}