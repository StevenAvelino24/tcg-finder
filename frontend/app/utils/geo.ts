export const getGeoBoundingBox = (lat: number, lon: number, radius: number) => {
    const deltaLat = radius / 111.1;
    const deltaLon = radius / (111.1 * Math.cos(lat * (Math.PI / 180)));

    const minLat = lat - deltaLat;
    const maxLat = lat + deltaLat;
    const minLon = lon - deltaLon;
    const maxLon = lon + deltaLon;

    return {
        topLeft: {
            lat: maxLat,
            lon: minLon
        },
        bottomRight: {
            lat: minLat,
            lon: maxLon
        }
    };
}