import type { Shop } from "~/types/shop"

export const useShop = () => {
    const shop = useState<Shop | null>('user-shop', () => null);

    const isEnabled = computed(() => shop?.value?.enabled);
    const isSelling = computed(() => shop?.value?.selling);

    return { shop, isEnabled, isSelling };
}