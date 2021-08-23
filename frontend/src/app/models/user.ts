import { Role } from "./role";

export class User {
    id: number;
    display_name?: string;
    username?: string;
    password?: string;
    firstName?: string;
    lastName?: string;
    role?: Role;
    token?: string;
    decodedToken?:any;
    expirationDate?:string;
    isExpired?:string;
    rawToken?:string;
    osp_number?:any;
    osp_details?:any;
}