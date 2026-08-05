export interface LocationAttrs {
  origin: string;
  geom_quadindex: string;
  zoomlevel: number;
  featureId: string;
  lon: number;
  lat: number;
  label: string;
  detail: string;
  rank: number;
}

export interface LocationResult {
  id: number;
  weight: number;
  attrs: LocationAttrs;
}

export interface SearchResponse {
  results: LocationResult[];
}