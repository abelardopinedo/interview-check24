export interface LogSummary {
  id: number
  endpoint: string
  httpMethod: string
  statusCode: number | null
  latency: number | null
  createdAt: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    page: number
    limit: number
    total: number
    pages: number
  }
}

export interface ProviderLog {
  providerName: string
  status: string
  httpCode: number | null
  latency: number | null
  requestPayload: string | null
  responsePayload: string | null
  url: string
  errorMessage: string | null
}

export interface LogDetail extends LogSummary {
  requestPayload: string
  responsePayload: string
  providerLogs: ProviderLog[]
}

export interface ProviderPerformance {
  providerName: string
  avgLatency: number
  errorCount: number
  totalCount: number
}

export type PerformanceStats = ProviderPerformance[]
