export interface Provider {
  id: number
  name: string
  url: string
  has_discount: boolean
  internalKey: string
}

export interface UpdateProvider {
  name?: string
  url?: string
  has_discount?: boolean
}
