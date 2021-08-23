export interface Mail {
    id: number;
    subject: string;
    body_content: string;
    sent_date: string;
    signature_id: number;
    auditors: number;
    partners: number;
    consultants: number;
    subscribers: number;
    clients: number;
    status?: number; 
}