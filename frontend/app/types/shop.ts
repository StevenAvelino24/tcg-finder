import type { Game } from "./game";
import type { Image } from "./image";
import type { ShopEvent } from "./shop-event";

export interface Shop {
    slug: string,
    title: string,
    address: string,
    city: string,
    state: string,
    zipcode: string,
    openingHours: string|null,
    phone: string|null,
    email: string|null,
    images: Image[],
    selling: boolean,
    latitude: number,
    longitude: number,
    games: Game[],
    enabled: boolean,
    description: string|null,
    events: ShopEvent[]
};