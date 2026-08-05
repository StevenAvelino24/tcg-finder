import type { Game } from "./game";

export interface ShopEvent {
    name: string,
    game: Game,
    format: string,
    numberParticipants: number,
    startDateTime: Date,
    endDateTime: Date,
    slug: string,
    currentNumberParticipants: number
};