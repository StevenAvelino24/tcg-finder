import type { Shop } from "./shop";

export interface User {
    id: number,
    email: string,
    firstName: string,
    lastName?: string,
    roles: string[],
    shops: Shop[],
    isVerified: boolean
};