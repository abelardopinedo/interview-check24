export interface Quote {
  provider: string;
  price: number;
  currency: string;
  discount_price?: number | null;
}
