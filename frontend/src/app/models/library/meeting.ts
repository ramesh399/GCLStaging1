export interface Meeting {
    id: number;
    location: string;
    type: number;
    attendees?: string;
    apologies?: string;
    status?: number; 
    meeting_id?: number;
    raised_id?: number;
    class: string;
    minute_date: string;
    details?: string;
}